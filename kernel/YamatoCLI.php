<?php namespace Kernel;

use DirectoryIterator;
use Kernel\Database\Database;

/**
 * Class YamatoCLI
 *
 * @package Kernel
 */
final class YamatoCLI
{
    /**
     * Holds command array of arguments
     */
    private $command = null;


    /**
     * Get the arguments on instantiation
     *
     * @param $arguments
     */
    public function __construct($arguments = null)
    {
        if (!isset($arguments[1])) {

            /**
             * Yamato Cover
             */
            echo <<<EOF


           ..      ..   ..     ..    -#      ..
 ,#   x#  - ,#=  ##;;##+,+#   -..x# ++#++ .#+,-#X
  ##  #    ,.##  #.  =#   #+   ,,-#  ,#   #+    #.
   #-#+  #X  +#  #   =#   #+ -#.  #. -#   #=    #.
    ##   #X.,##  #.  +#   #x =#..x#. .#-. .#+,-#x
   ;#     ,,.    .    .   .    ;,      ;,   ,;;
  +X

 Yamato is a Command Line Interface(CLI) tool that is
 made to interact with the Strife Framework.


     COMMANDS                 ARGUMENTS                 DESCRIPTION
--------------------	--------------------    -----------------------------

[Cleanup]
  clear:all                                     clear all dirs(backups excluded)
  clear:sessions                                clear sessions directory
  clear:logs                                    clear logs directory
  clear:backups                                 clear backups directory

[Generators]
  create:model          [name] [table=null]     create a model class
  create:controller     [name] [empty=null]     create a controller class
  create:migration      [name] [table=null]     create a migration class
  create:process        [name]                  create a process class
  create:request        [name]                  create a request class
  create:seeder         [name] [model=null]     create a database seeder class
  create:key                                    create a new site/application key

[Database]
  db:migrate                                    install all migrations
  db:rollback                                   rollback all migrations
  db:table:up           [class]                 migrate a specific table using model class
  db:table:down         [class]                 rollback a specific table using model class
  db:backup                                     backup database's current state
  db:restore                                    restore last made backup on database
  db:seed                                       carry-out database seeding

[Security]
  hash:encode           [string]                returns the hash of a given string
  hash:verify           [string] [hashed]       verify whether data matches the hashed value
  cipher:encrypt        [string]                encrypt a string user caesar cipher algorithm
  cipher:decrypt        [string]                decrypt an encrypted cipher text

EOF;
        } else {
            $this->command = $arguments;
            return $this->parseCommand();
        }

        return;
    }


    /**
     * Command Parser
     *
     * @return mixed
     */
    private function parseCommand()
    {
        /**
         * Cleaning
         */
        if ($this->command[1] == 'clear:all') {
            $this->clear('sessions');
            $this->clear('logs');
            return die("\nall trash cleared.\n");
        } elseif ($this->command[1] == 'clear:sessions') {
            $this->clear('sessions');
            return die("\nsessions directory cleared.\n");
        } elseif ($this->command[1] == 'clear:logs') {
            $this->clear('logs');
            return die("\nlogs directory cleared.\n");
        } elseif ($this->command[1] == 'clear:backups') {
            $this->clear('backups');
            return die("\nbackups directory cleared.\n");
        }

        /**
         * Generators
         */
        elseif ($this->command[1] == 'create:model') {
            if (isset($this->command[2])) {
                $option = isset($this->command[3]) ? $this->command[3] : strtolower($this->command[2]);
                return $this->createModel($this->command[2], $option);
            } else {
                die("\ntoo few arguments, create:model expects [name], [table] is optional\n");
            }
        } elseif ($this->command[1] == 'create:controller') {
            if (isset($this->command[2])) {
                $option = isset($this->command[3]) ? $this->command[3] : null;
                return $this->createController($this->command[2], $option);
            } else {
                die("\ntoo few arguments, create:controller expects [name], [empty] is optional\n");
            }
        } elseif ($this->command[1] == 'create:migration') {
            if (isset($this->command[2]) && isset($this->command[3])) {
                return $this->createMigration($this->command[2], $this->command[3]);
            } else {
                die("\ntoo few arguments, create:migration expects [name] [table]\n");
            }
        } elseif ($this->command[1] == 'create:request') {
            if (isset($this->command[2])) {
                return $this->createRequest($this->command[2]);
            } else {
                die("\ncreate:request expects parameter [name]\n");
            }
        } elseif ($this->command[1] == 'create:process') {
            if (isset($this->command[2])) {
                return $this->createProcess($this->command[2]);
            } else {
                die("\ncreate:process expects parameter [name]\n");
            }
        } elseif ($this->command[1] == 'create:seeder') {
            if (isset($this->command[2]) && isset($this->command[3])) {
                return $this->createSeeder($this->command[2], $this->command[3]);
            } else {
                die("\ntoo few arguments, create:seeder expects [name] [table]\n");
            }
        } elseif ($this->command[1] == 'create:key') {
            return die("\n" . Hash::generateSalt() . "\n");
        }
        /**
         * Database and Migrations
         */
        elseif ($this->command[1] == 'db:migrate') {
            return $this->migrate('up');
        } elseif ($this->command[1] == 'db:rollback') {
            return $this->migrate('down');
        } elseif ($this->command[1] == 'db:table:up') {
            return $this->tableMigration($this->command[2], 'up');
        } elseif ($this->command[1] == 'db:table:down') {
            return $this->tableMigration($this->command[2], 'down');
        } elseif ($this->command[1] == 'db:backup') {
            return $this->backup();
        } elseif ($this->command[1] == 'db:restore') {
            return $this->restore();
        } elseif ($this->command[1] == 'db:seed') {
            return $this->seed();
        }

        /**
         * Security
         */
        elseif ($this->command[1] == 'hash:encode') {
            if (!isset($this->command[2])) {
                die("\nhash:verify expects [data]\n");
            }
            return die("\n" . Hash::encode(trim($this->command[2], ' ')));
        } elseif ($this->command[1] == 'hash:verify') {
            if (!isset($this->command[2]) || !isset($this->command[3])) {
                die("\ntoo few arguments, hash:verify expects [data] and [hashed] value\n");
            }
            return (Hash::verify($this->command[2], $this->command[3])) ? die("\ntrue\n") : die("\nfalse\n");
        } elseif ($this->command[1] == 'cipher:encrypt') {
            if (!isset($this->command[2])) {
                die("\ncipher:encrypt expects [string]\n");
            }
            return die("\n" . Cipher::encrypt($this->command[2]) . "\n");
        } elseif ($this->command[1] == 'cipher:decrypt') {
            if (!isset($this->command[2])) {
                die("\ncipher:encrypt expects [string]\n");
            }
            return die("\n" . Cipher::decrypt($this->command[2]) . "\n");
        } else {
            die("\nerror: unknown command '{$this->command[1]}' type 'help' for information.\n");
        }
    }


    /**
     * Clear garbage files inside storage.
     *
     * @param $folder
     * @var $filename
     * @return boolean
     */
    private function clear($folder)
    {
        $container = storage_dir() . $folder;
        $handle = new DirectoryIterator($container);

        foreach ($handle as $file) {
            if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                continue;
            }
            if (is_file("{$container}/{$file->getFilename()}")) {
                unlink("{$container}/{$file->getFilename()}");
            }
        }

        return true;
    }


    /**
     * Create new model class
     *
     * @param $name
     * @param $table
     * @var $container
     * @var $name
     * @var $data
     * @var $file
     * @return string
     */
    private function createModel($name, $table)
    {
        $container = app_dir() . 'models';
        $name = Formatter::stripSpecialChars(ucfirst($name));
        $table = Formatter::stripSpecialChars($table);
        $data = <<<EOF
<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;

class {$name} extends Model
{
    protected static \$table = "{$table}";
}
EOF;

        if (file_exists("{$container}/{$name}.php")) {
            return die("\nmodel '{$name}' already exists\n");
        }

        $file = fopen("{$container}/{$name}.php", 'x');
        fwrite($file, $data);
        exec('composer dump-autoload');

        return die("\n'{$name}' model class created.\n");
    }


    /**
     * Create a new controller class
     *
     * @param $name
     * @param $option
     *
     * @var $container
     * @var $name
     * @var $append
     * @var $methods
     * @var $data
     * @var $file
     */
    private function createController($name, $option = null)
    {
        $container = app_dir() . 'controllers';
        $name = preg_replace('/controller/i', 'Controller', ucfirst($name));
        $append = <<<EOF

    /**
     * Controller Index
     *
     * @return view
     **/
    public function index()
    {

    }


    /**
     * Fetch resource
     *
     * @return mixed
     **/
    public function fetch()
    {

    }


    /**
     * Show all/a resource(s)
     *
     * @param \$id
     * @return mixed
     **/
    public function show(\$id)
	{

	}


    /**
     * Create a resource
     *
     * @return view
     * */
    public function create()
    {

    }


    /**
     * Store the resource
     *
     * @return view
     * */
    public function store()
    {

    }


    /**
     * Edit a resource
     *
     * @param \$id
     * @return view
     */
    public function edit(\$id)
    {

    }


    /**
     * update the resource
     *
     * @return mixed
     */
    public function update()
    {

    }


    /**
     * Destroy a resource
     *
     * @param \$id
     */
    public function destroy(\$id)
    {

    }


EOF;

        /**
         * if $option is 'empty', return an empty class
         */
        $methods = ($option == 'empty') ? '' : $append;

        $data = <<<EOF
<?php

class {$name}
{
    {$methods}
}

EOF;

        if (file_exists("{$container}/{$name}.php")) {
            return die("\ncontroller '{$name}' already exists\n");
        }

        $file = fopen("{$container}/{$name}.php", 'x');
        fwrite($file, $data);

        return die("\n'{$name}' class created.\n");
    }


    /**
     * Create a migration class
     *
     * @param $name
     * @param $table
     *
     * @var $container
     * @var $name
     * @var $append
     * @var $methods
     * @var $data
     * @var $file
     */
    private function createMigration($name, $table)
    {
        $container = app_dir() . 'migrations';
        $name = preg_replace('/migration/i', 'Migration', ucfirst($name));
        $data = <<<EOF
<?php namespace App\Migrations;

use Kernel\Database\Migration;

class {$name} extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected \$table = "{$table}";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        \$this->increments('id');
    }


    /**
     * Install the migration
     *
     * @return void
     */
    public function up()
    {
        return \$this->install();
    }


    /**
     * Drop the table
     *
     * @return void
     */
    public function down()
    {
        return \$this->uninstall();
    }

}
EOF;

        if (file_exists("{$container}/{$name}.php")) {
            return die("\nmigration '{$name}' already exists\n");
        }

        $file = fopen("{$container}/{$name}.php", 'x');
        fwrite($file, $data);
        exec('composer dump-autoload');

        return die("\n'{$name}' class created.\n");
    }


    /**
     * Create a request class
     *
     * @param $name
     * @var $container
     * @var $name
     * @var $append
     * @var $methods
     * @var $data
     * @var $file
     */
    private function createRequest($name)
    {
        $container = app_dir() . 'requests';
        $name = preg_replace('/request/i', 'Request', ucfirst($name));
        $data = <<<EOF
<?php namespace App\Requests;

use Kernel\Request;

class {$name} extends Request
{

    /**
     * This is the route that will be used
     * to redirect when errors are present
     */
    protected \$route = '/';


    /**
     * Rules to be followed by request
     */
    protected \$rules = [];

}
EOF;

        if (file_exists("{$container}/{$name}.php")) {
            return die("\nrequest '{$name}' already exists\n");
        }

        $file = fopen("{$container}/{$name}.php", 'x');
        fwrite($file, $data);
        exec('composer dump-autoload');

        return die("\n'{$name}' class created.\n");
    }


    /**
     * @param $name
     * @param $model
     */
    private function createSeeder($name, $model)
    {
        if (!file_exists(app_dir() . "models/$model.php")) {
            return die("Model '$model' does not exist");
        }
        $container = app_dir() . 'seeders/';
        $name = preg_replace('/seeder/i', 'Seeder', ucfirst($name));
        $model = ucfirst($model);
        $data = <<<EOF
<?php namespace App\Seeders;

use App\Models\\{$model};

/**
 * Class {$name}
 *
 * @package App\Seeders
 */
class {$name}
{
    /**
     * Seed the database table
     */
    public function __construct()
    {
        $model::insert([

        ]);
    }
}
EOF;

        if (file_exists("{$container}/{$name}.php")) {
            return die("\nseeder '{$name}' already exists\n");
        }

        $file = fopen("{$container}/{$name}.php", 'x');
        fwrite($file, $data);
        exec('composer dump-autoload');

        return die("\n'{$name}' class created.\n");
    }


    /**
     * Create a process class
     *
     * @param $name
     * @var $container
     * @var $name
     * @var $append
     * @var $methods
     * @var $data
     * @var $file
     */
    private function createProcess($name)
    {
        $container = app_dir() . 'processes';
        $name = preg_replace('/process/i', 'Process', ucfirst($name));
        $data = <<<EOF
<?php

/**
 * TODO: Execute a process to separate the logic from it's controller.
 * Class {$name}
 */
class {$name}
{
    /**
     * Execute the Process
     *
     * @todo execute
     * @param \$callback
     * @return mixed
     */
    public function execute(\$callback = "")
    {
        //logic here

        //return callback on fail
        return \$callback();
    }
}
EOF;

        if (file_exists("{$container}/{$name}.php")) {
            return die("\nprocess '{$name}' already exists\n");
        }

        $file = fopen("{$container}/{$name}.php", 'x');
        fwrite($file, $data);
        exec('composer dump-autoload');

        return die("\n'{$name}' class created.\n");
    }


    /**
     * Install/Uninstall all migrations
     *
     * @param $action
     * @var $container
     * @var $migration
     * @return mixed
     */
    private function migrate($action)
    {
        $directory = app_dir() . 'migrations';
        $container = new DirectoryIterator($directory);
        $message = ($action == 'down') ? "\ndatabase rolled back.\n" : "\ndatabase successfully migrated.\n";

        foreach ($container as $handle) {
            if (is_file("{$directory}/{$handle->getFilename()}")) {
                $migration = "App\\Migrations\\" . $handle->getBasename('.php');
                $migration = new $migration();
                $migration->$action();
            }
        }

        return die($message);
    }


    /**
     * Backup database's current state
     *
     * @return string
     */
    private function backup() {
        $container = app_dir() . 'models/';
        $iterator = new DirectoryIterator($container);

        foreach ($iterator as $it) {
            if (!is_file($container . $it->getFilename())) {
                continue;
            }

            $model =  'App\Models\\' . str_replace('.php', '', $it->getFilename());
            $model = new $model();
            $model->backup();
        }

        return die("\nDatabase tables backed up.\n");
    }


    /**
     * Restore last backed up table state
     *
     * @return string
     */
    private function restore() {
        $container = app_dir() . 'models/';
        $iterator = new DirectoryIterator($container);

        foreach ($iterator as $it) {
            if (!is_file($container . $it->getFilename())) {
                continue;
            }

            $model =  'App\Models\\' . str_replace('.php', '', $it->getFilename());
            $model = new $model();
            $model->restore();
        }

        return die("\nDatabase restored.\n");
    }


    /**
     * Perform database seeding
     *
     * @return string
     */
    private function seed() {
        $container = app_dir() . 'seeders/';
        $iterator = new DirectoryIterator($container);

        foreach ($iterator as $it) {
            if (!is_file($container . $it->getFilename())) {
                continue;
            }

            $seeder =  'App\Seeders\\' . str_replace('.php', '', $it->getFilename());
            new $seeder();
        }

        return die("\nSeeding completed.\n");
    }


    /**
     * Install/Uninstall a migration table
     *
     * @param $name
     * @param $action
     * @var $container
     * @var $migration
     * @return mixed
     */
    private function tableMigration($name, $action)
    {
        $className = "App\\Migrations\\" . $name;

        if (file_exists("{$className}.php")) {
            if (class_exists($className)) {
                $migration = new $className();
                $migration->$action();

                $message = ($action == 'down') ?
                    "\ntable rolled back.\n" : "\ntable successfully migrated.\n";
            } else {
                return die("\n'{$name}' class does not exist.\n");
            }
        } else {
            return die("\n'{$name}' file does not exist.\n");
        }

        return die($message);
    }
}