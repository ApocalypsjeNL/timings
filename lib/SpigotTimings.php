<?php

/**
 * Spigot Timings Parser
 *
 * Written by Aikar <aikar@aikar.co>
 *
 * @license MIT
 */
class SpigotTimings {
    use Singleton;
    public $id;

    private $data;
    /**
     * @var StorageService
     */
    private $storage;

    public static function bootstrap() {
        $timings = self::getInstance();
        $timings->prepareData();
        if (!empty($_GET['raw'])) {
            // Process Raw load before template
            $timings->loadData();
        }
        Template::render();
    }

    public function prepareData() {
        /**
         * @var StorageService $storage
         */
        $storage = new CacheStorage();
        $id = null;

        if (!empty($_GET['url'])) {
            $id = $_GET['url'];
            $storage = new LegacyStorageService();
        } else if (!empty($_GET['id'])) {
            $id = $_GET['id'];
        } else if (TIMINGS_ENV == 'dev') {
            $id = '1a0307e706ae4170b6be56442951d45a'; // DEV test
        }
        $id = Util::sanitizeHex($id);
        $this->id = $id;
        $this->storage = $storage;

        if ($storage instanceof LegacyStorageService || TIMINGS_ENV != 'dev') {
            LegacyHandler::load(trim($storage->get($id)));
            exit;
        }
    }

    public function loadData() {
        $id = $this->id;
        if ($id) {
            $this->data = trim($this->storage->get($id));

            if (!empty($_GET['raw'])) {
                header("Content-Type: text/plain");
                if (!empty($_GET['mini'])) {
                    echo $this->data;
                } else {
                    echo json_encode(json_decode($this->data), JSON_PRETTY_PRINT);
                }
                die;
            }
            $this->data = TimingsMaster::createObject(json_decode($this->data));
            $GLOBALS['timingsData'] = $this->data;
        }

    }
} 
