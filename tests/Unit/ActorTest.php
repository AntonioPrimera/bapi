<?php

namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Actor;
use AntonioPrimera\Bapi\Tests\TestCase;
use AntonioPrimera\Bapi\Tests\TestContext\CreateCompanyBapi;
use AntonioPrimera\Bapi\Tests\TestContext\Models\TestUser;
use Illuminate\Contracts\Auth\Authenticatable;

class ActorTest extends TestCase
{
	/** @test */
	public function it_can_show_that_no_user_is_logged_in()
	{
		$actor = new Actor();
		
		$this->assertTrue($actor->isGuest());
		$this->assertFalse($actor->isAuthenticated());
		$this->assertInstanceOf(Authenticatable::class, $actor);
	}
	
	/** @test */
	public function it_can_show_that_a_user_is_logged_in()
	{
		$user = new TestUser();
		$this->actingAs($user);
		$this->assertAuthenticated();
		
		$actor = new Actor();
		$this->assertTrue($actor->isAuthenticated());
		$this->assertFalse($actor->isGuest());
		$this->assertSame($user, $actor->getModel());
	}
	
	/** @test */
	public function it_forwards_attribute_and_method_calls_to_the_model()
	{
		$user = new TestUser();
		$this->actingAs($user);
		$this->assertAuthenticated();
		
		$actor = new Actor();
		$this->assertSame($user, $actor->getModel());
		
		//attributes
		$this->assertEquals($user->special, $actor->special);
		
		//attributes getters and setters
		$actor->wonderful = 'Bapis Are Wonderful';
		$this->assertEquals('bapis-are-wonderful', $actor->wonderful);
		
		//methods
		$this->assertEquals($user->addition(5, 59), $actor->addition(5, 59));
	}
	
	/** @test */
	public function a_bapi_uses_a_gues_actor_if_no_authenticated_user()
	{
		$bapi = new CreateCompanyBapi();
		
		$this->assertInstanceOf(Actor::class, $bapi->actor());
		$this->assertFalse($bapi->actor()->isAuthenticated());
		$this->assertTrue($bapi->actor()->isGuest());
	}
	
	/** @test */
	public function a_bapi_uses_the_actor_class_to_manage_actors()
	{
		$user = new TestUser();
		$this->actingAs($user);
		$this->assertAuthenticated();
		
		$bapi = new CreateCompanyBapi();
		
		$this->assertInstanceOf(Actor::class, $bapi->actor());
		$this->assertTrue($bapi->actor()->isAuthenticated());
		$this->assertSame($user, $bapi->actor()->getModel());
	}
	
	/** @test */
	public function a_bapi_acting_as_an_user_will_have_that_user_as_an_actor()
	{
		$user = new TestUser();
		//$this->actingAs($user);
		$this->assertGuest();
		
		$bapi = new CreateCompanyBapi();
		
		//guest
		$this->assertInstanceOf(Actor::class, $bapi->actor());
		$this->assertFalse($bapi->actor()->isAuthenticated());
		$this->assertTrue($bapi->actor()->isGuest());
		$this->assertNull($bapi->actor()->getModel());
		
		//authenticate only for the bapi
		$bapi->actingAs($user);
		$this->assertGuest();
		$this->assertInstanceOf(Actor::class, $bapi->actor());
		$this->assertTrue($bapi->actor()->isAuthenticated());
		$this->assertSame($user, $bapi->actor()->getModel());
		
		//de-authenticate the bapi actor
		$bapi->actingAs(null);
		$this->assertInstanceOf(Actor::class, $bapi->actor());
		$this->assertFalse($bapi->actor()->isAuthenticated());
		$this->assertTrue($bapi->actor()->isGuest());
		$this->assertNull($bapi->actor()->getModel());
	}
	
	/** @test */
	public function it_has_some_syntactic_sugar_which_must_be_tested()
	{
		$user = new TestUser();
		$this->actingAs($user);
		$this->assertAuthenticated();
		
		$bapi = new CreateCompanyBapi();
		
		$this->assertSame($user, $bapi->actor()->user);
		$this->assertSame($user, $bapi->actor()->model);
		$this->assertTrue($bapi->actor()->isAuthenticated);
		$this->assertFalse($bapi->actor()->isGuest);
	}
}