<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Post;
use App\User;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store()
    {
        // $this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST','/api/posts', [
            'title' => 'El post de prueba'
        ]);

        $response->assertJsonStructure(['id','title','created_at','updated_at']) // Valida que tenga la estructura definida
                 ->assertJson(['title' => 'El post de prueba']) //validamos que el elemente si exista
                 ->assertStatus(201); //Ok, Creado el recurso

        $this->assertDatabaseHas('posts', ['title' => 'El post de prueba']); //cuarta validaciôn: que el datos haya quedado en la BD
    }

    public function test_validate_title(){

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST','/api/posts', [
            'title' => ''
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('title');
    }

    public function test_show(){

        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET',"/api/posts/$post->id"); //id = 1

        $response->assertJsonStructure(['id','title','created_at','updated_at']) // Valida que tenga la estructura definida
        ->assertJson(['title' => $post->title]) //validamos que el elemente si exista
        ->assertStatus(200); //Ok, Creado el recurso

    }

    public function test_404_show(){

        $user = factory(User::class)->create();
        factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET','/api/posts/1000'); //id = 1

        $response->assertStatus(404); //Ok, Creado el recurso

    }


    public function test_update()
    {

        // $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT',"/api/posts/$post->id", [
            'title' => 'nuevo titulo'
        ]);

        $response->assertJsonStructure(['id','title','created_at','updated_at'])
                 ->assertJson(['title' => 'nuevo titulo'])
                 ->assertStatus(200); //Ok, Creado el recurso

        $this->assertDatabaseHas('posts', ['title' => 'nuevo titulo']); //cuarta validaciôn: que el datos haya quedado en la BD
    }

    public function test_delete()
    {
        // $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE',"/api/posts/$post->id");

        $response->assertSee(null) //validamos que el elemente si exista
                 ->assertStatus(204); //sin contenido

        $this->assertDatabaseMissing('posts', ['id' => $post->id]); //cuarta validaciôn: que el datos haya quedado en la BD
    }

    public function test_index(){

        $user = factory(User::class)->create();
        factory(Post::class, 5)->create();

        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts');

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id','title','created_at','updated_at']
            ]
        ])->assertStatus(200); //ok
    }

    public function test_guest(){

        $this->json('GET',  '/api/posts')->assertStatus(401); //no esta autorizado al acceso
        $this->json('POST', '/api/posts')->assertStatus(401); //no esta autorizado al acceso
        $this->json('GET',  '/api/posts/1000')->assertStatus(401); //no esta autorizado al acceso
        $this->json('PUT',  '/api/posts/1000')->assertStatus(401); //no esta autorizado al acceso
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401); //no esta autorizado al acceso
    }
}