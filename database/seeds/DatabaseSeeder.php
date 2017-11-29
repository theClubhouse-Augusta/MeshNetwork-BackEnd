<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // constants
        $USERS = 750;
        $SPACES = 5;
        $EVENTS = 200;
        $OPTS = ($EVENTS * 5);   
        $APPEARANCES = ($USERS * 10);
        // $this->call(UsersTableSeeder::class);
        factory(App\User::class, $USERS)->create();
        factory(App\Event::class, $EVENTS)->create();
        factory(App\Opt::class, $OPTS)->create();
        factory(App\Appearance::class, $APPEARANCES)->create();
        factory(App\Workspace::class, $SPACES)->create();
    }
}
