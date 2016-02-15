<?php namespace App\Migrations;

use Kernel\Database\Migration;

class UsersTableMigration extends Migration
{

    /**
     * Name of the table to migrate
     **/
    protected $table = "users";


    /**
     * Setup field names data types
     * for this table
     **/
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('firstname');
        $this->varchar('lastname');
        $this->char('username', 128, 'unique');
        $this->char('password', 128, 'unique');
        $this->char('email', 128, 'unique');
        $this->varchar('number', 50);
        $this->varchar('avatar', 255);
        $this->varchar('role', 20);
        $this->varchar('active', 20);
        $this->varchar('date_added', 50);
        $this->varchar('time_added', 50);
        $this->char('remember_token', 32, null, 'null');
    }


    /**
     * Install the migration
     *
     * @return void
     **/
    public function up()
    {
        return $this->install();
    }


    /**
     * Drop the table
     *
     * @return void
     **/
    public function down()
    {
        return $this->uninstall();
    }

}