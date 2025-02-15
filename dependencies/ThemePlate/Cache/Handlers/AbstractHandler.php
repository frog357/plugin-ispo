<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace CardanoPress\ISPO\Dependencies\ThemePlate\Cache\Handlers;

use CardanoPress\ISPO\Dependencies\ThemePlate\Cache\Storages\StorageInterface;
use CardanoPress\ISPO\Dependencies\ThemePlate\Process\Tasks;

abstract class AbstractHandler implements HandlerInterface {

	protected StorageInterface $storage;
	protected ?Tasks $tasks;


	public function __construct( StorageInterface $storage, Tasks $tasks = null ) {

		$this->storage = $storage;
		$this->tasks   = $tasks;

	}


	protected function forced_refresh( string $key ): bool {

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $_REQUEST[ StorageInterface::PREFIX . 'refresh' ] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return in_array( $key, (array) $_REQUEST[ StorageInterface::PREFIX . 'refresh' ], true );

	}


	protected function background_update(): bool {

		if ( ! $this->tasks instanceof Tasks ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return isset( $_REQUEST['action'] ) && ( $this->tasks->get_identifier() === $_REQUEST['action'] );

	}


	protected function action_update( string $key, array $data ) {

		if ( ! $this->tasks instanceof Tasks ) {
			return $this->set( $key, $data );
		}

		$storage = get_class( $this->storage );
		$pointer = $this->storage->pointer();

		$this->tasks->add( array( static::class, 'update' ), array( $storage, $pointer, $key, $data ) );

		return false;

	}


	public static function update( string $storage, int $pointer, string $key, array $data ) {

		$handler = static::class;
		/** @var StorageInterface $storage */
		$storage = new $storage();

		$storage->point( $pointer );

		return ( new $handler( $storage ) )->set( $key, $data );

	}

}
