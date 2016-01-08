<?php namespace Kernel;

use DirectoryIterator;
use Kernel\Database\Database;

/**
 * Class YamatoCLI
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


Type help:[command_name] for command information.


COMMANDS:               ARGUMENTS:               DESCRIPTION:

cleanups
  clear:all                                      clear all garbage data
  clear:sessions                                 clear sessions directory
  clear:logs                                     clear logs directory

generators
  create:model          [name] [table]           generate model class
  create:controller     [name] [empty]           generate controller class
  create:migration      [name] [table]           generate migration class
  create:process        [name]                   generate a process class
  create:request        [name]                   generate a request class
  create:key                                     create a new site key

database
  db:create             [name]                   create new database
  db:migrate                                     install all migrations
  db:rollback                                    rollback all migrations
  db:table:up           [class]                  migrate a specific table using given class
  db:table:down         [class]                  rollback a specific table using given class

hash
  hash:encode           [string]                 returns the hash of a given string
  hash:verify           [data] [hashed]          verify whether data matches the hashed value

EOF;
        } else {
            $this->command = $arguments;
            return $this->parseCommand();
        }

        return;
    }


    /**
     * Command Parser
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
        }
        elseif ($this->command[1] == 'clear:sessions') {
            $this->clear('sessions');
            return die("\nsessions directory cleared.\n");
        }
        elseif ($this->command[1] == 'clear:logs') {
            $this->clear('logs');
            return die("\nlogs directory cleared.\n");
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
        }
        elseif ($this->command[1] == 'create:controller') {
            if (isset($this->command[2])) {
                $option = isset($this->command[3]) ? $this->command[3] : null;
                return $this->createController($this->command[2], $option);
            } else {
                die("\ntoo few arguments, create:controller expects [name], [empty] is optional\n");
            }
        }
        elseif ($this->command[1] == 'create:migration') {
            if (isset($this->command[2]) && isset($this->command[3])) {
                return $this->createMigration($this->command[2], $this->command[3]);
            } else {
                die("\ntoo few arguments, create:migration expects [name] [table]\n");
            }
        }
        elseif ($this->command[1] == 'create:request') {
            if (isset($this->command[2])) {
                return $this->createRequest($this->command[2]);
            } else {
                die("\ncreate:request expects parameter [name]\n");
            }
        }
        elseif ($this->command[1] == 'create:process') {
            if (isset($this->command[2])) {
                return $this->createProcess($this->command[2]);
            } else {
                die("\ncreate:process expects parameter [name]\n");
            }
        }
        elseif ($this->command[1] == 'create:key') {
            return die(Hash::generateSalt());
        }

        /**
         * Database and Migrations
         */
        elseif ($this->command[1] == 'db:create') {
            $db = new Database();
            $db->create(filter_var($this->command[2], FILTER_SANITIZE_STRIPPED));
            return die("\ndatabase {$this->command[2]} created.\n");
        }
        elseif ($this->command[1] == 'db:migrate') {
            return $this->migrate('up');
        }
        elseif ($this->command[1] == 'db:rollback') {
            return $this->migrate('down');
        }
        elseif ($this->command[1] == 'db:table:up') {
            return $this->tableMigration($this->command[2],'up');
        }
        elseif ($this->command[1] == 'db:table:down') {
            return $this->tableMigration($this->command[2],'down');
        }

        /**
         * Hash
         */
        elseif ($this->command[1] == 'hash:encode') {
            if (!isset($this->command[2])) {
                die("\nhash:verify expects [data]\n");
            }
            return die("\n".Hash::encode($this->command[2])."\n");
        }
        elseif ($this->command[1] == 'hash:verify') {
            if (!isset($this->command[2]) || !isset($this->command[3])) {
                die("\ntoo few arguments, hash:verify expects [data] and [hashed] value\n");
            }
            return (Hash::verify($this->command[2],$this->command[3])) ? die("\ntrue\n") : die("\nfalse\n");
        }
        else{
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
        $container = "storage/" . $folder;
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
        $container = 'app/models';
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

        if(file_exists("{$container}/{$name}.php")){
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
        $container = 'app/controllers';
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

        if(file_exists("{$container}/{$name}.php")){
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
        $container = 'app/migrations';
        $name = preg_replace('/migration/i','Migration', ucfirst($name));
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

        if(file_exists("{$container}/{$name}.php")){
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
        $container = 'app/requests';
        $name = preg_replace('/request/i','Request', ucfirst($name));
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

        if(file_exists("{$container}/{$name}.php")){
            return die("\nrequest '{$name}' already exists\n");
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
        $container = 'app/processes';
        $name = preg_replace('/process/i','Process', ucfirst($name));
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

        if(file_exists("{$container}/{$name}.php")){
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
        $container = new DirectoryIterator('app/migrations');
        $message = ($action == 'down') ? "\ndatabase rolled back.\n": "\ndatabase successfully migrated.\n";

        foreach ($container as $handle) {
            if(is_file("app/migrations/{$handle->getFilename()}"))
            {
                $migration = "App\\Migrations\\".$handle->getBasename('.php');
                $migration = new $migration();
                $migration->$action();
            }
        }

        return die($message);
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
        $className = "App\\Migrations\\".$name;

        if(file_exists("{$className}.php")){
            if(class_exists($className))
            {
                $migration = new $className();
                $migration->$action();

                $message = ($action == 'down') ?
                    "\ntable rolled back.\n": "\ntable successfully migrated.\n";
            }else{
                return die("\n'{$name}' class does not exist.\n");
            }
        }else{
            return die("\n'{$name}' file does not exist.\n");
        }

        return die($message);
    }
}