<?php

namespace AntonioPrimera\Bapi\Tests\TestContext\Models;

class TestUser extends \Illuminate\Database\Eloquent\Model
{
	protected $connection = 'testbench';
	protected $table = 'test_users';
	protected $guarded = [];
	public $timestamps = false;
	
}