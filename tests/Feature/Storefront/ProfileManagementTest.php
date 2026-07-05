<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can update name phone and bio', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Jane Doe',
        'email' => $user->email,
        'phone' => '+8801711223344',
        'bio' => 'Hello there.',
        'gender' => 'female',
        'date_of_birth' => '1995-05-05',
    ]);

    $response->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->name)->toBe('Jane Doe');
    expect($user->phone)->toBe('+8801711223344');

    $profile = $user->profile()->first();
    expect($profile->bio)->toBe('Hello there.');
    expect($profile->gender)->toBe('female');
    expect($profile->date_of_birth->format('Y-m-d'))->toBe('1995-05-05');
});

test('user can upload an avatar and the old one is cleaned up on replace', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $first = UploadedFile::fake()->image('avatar1.jpg');

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $first,
    ]);

    $profile = $user->profile()->first();
    $firstPath = $profile->avatar_path;

    Storage::disk('public')->assertExists($firstPath);

    $second = UploadedFile::fake()->image('avatar2.jpg');

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $second,
    ]);

    $profile->refresh();

    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($profile->avatar_path);
    expect($profile->avatar_path)->not->toBe($firstPath);
});

test('user can create an address and it becomes default when it is the first one', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('profile.addresses.store'), [
        'label' => 'Home',
        'line1' => '123 Main St',
        'city' => 'Dhaka',
        'state' => 'Dhaka',
        'postal_code' => '1207',
        'country' => 'Bangladesh',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'label' => 'Home',
        'is_default' => true,
    ]);
});

test('only one address can be default at a time', function () {
    $user = User::factory()->create();
    $existingDefault = Address::factory()->for($user)->default()->create();

    $this->actingAs($user)->post(route('profile.addresses.store'), [
        'label' => 'Office',
        'line1' => '456 Business Rd',
        'city' => 'Dhaka',
        'state' => 'Dhaka',
        'postal_code' => '1208',
        'country' => 'Bangladesh',
        'is_default' => '1',
    ]);

    expect($existingDefault->refresh()->is_default)->toBeFalse();
    $this->assertDatabaseHas('addresses', ['user_id' => $user->id, 'label' => 'Office', 'is_default' => true]);
});

test('user can update their address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create(['label' => 'Home']);

    $this->actingAs($user)->patch(route('profile.addresses.update', $address), [
        'label' => 'Updated Home',
        'line1' => $address->line1,
        'city' => $address->city,
        'state' => $address->state,
        'postal_code' => $address->postal_code,
        'country' => $address->country,
    ]);

    expect($address->refresh()->label)->toBe('Updated Home');
});

test('user cannot update another users address', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->for($otherUser)->create();

    $this->actingAs($user)->patch(route('profile.addresses.update', $address), [
        'label' => 'Hacked',
        'line1' => $address->line1,
        'city' => $address->city,
        'state' => $address->state,
        'postal_code' => $address->postal_code,
        'country' => $address->country,
    ])->assertForbidden();
});

test('user can delete an address and default reassigns to another one', function () {
    $user = User::factory()->create();
    $default = Address::factory()->for($user)->default()->create();
    $other = Address::factory()->for($user)->create();

    $this->actingAs($user)->delete(route('profile.addresses.destroy', $default));

    $this->assertDatabaseMissing('addresses', ['id' => $default->id]);
    expect($other->refresh()->is_default)->toBeTrue();
});

test('user cannot delete another users address', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->for($otherUser)->create();

    $this->actingAs($user)->delete(route('profile.addresses.destroy', $address))
        ->assertForbidden();

    $this->assertDatabaseHas('addresses', ['id' => $address->id]);
});

test('user can set an address as default', function () {
    $user = User::factory()->create();
    $default = Address::factory()->for($user)->default()->create();
    $other = Address::factory()->for($user)->create();

    $this->actingAs($user)->patch(route('profile.addresses.set-default', $other));

    expect($default->refresh()->is_default)->toBeFalse();
    expect($other->refresh()->is_default)->toBeTrue();
});
