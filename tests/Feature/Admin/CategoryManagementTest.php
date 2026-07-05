<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function admin(): User
{
    return User::factory()->admin()->create();
}

test('admin can view the categories list', function () {
    Category::factory()->count(3)->create();

    $response = $this->actingAs(admin())->get(route('admin.categories.index'));

    $response->assertStatus(200);
});

test('non-admin cannot access category management', function () {
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->get(route('admin.categories.index'));

    $response->assertStatus(403);
});

test('admin can create a category', function () {
    $response = $this->actingAs(admin())->post(route('admin.categories.store'), [
        'name' => 'Electronics',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_active' => true,
    ]);
});

test('admin can create a subcategory', function () {
    $parent = Category::factory()->create();

    $response = $this->actingAs(admin())->post(route('admin.categories.store'), [
        'name' => 'Laptops',
        'parent_id' => $parent->id,
    ]);

    $response->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Laptops',
        'parent_id' => $parent->id,
    ]);
});

test('category slug is auto-generated when omitted', function () {
    $this->actingAs(admin())->post(route('admin.categories.store'), [
        'name' => 'Home & Garden',
    ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'Home & Garden',
        'slug' => 'home-garden',
    ]);
});

test('admin can update a category', function () {
    $category = Category::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs(admin())->put(route('admin.categories.update', $category), [
        'name' => 'New Name',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.categories.index'));

    expect($category->refresh()->name)->toBe('New Name');
});

test('unchecking active marks a category inactive', function () {
    $category = Category::factory()->create(['is_active' => true]);

    $response = $this->actingAs(admin())->put(route('admin.categories.update', $category), [
        'name' => $category->name,
        'is_active' => '0',
    ]);

    $response->assertRedirect(route('admin.categories.index'));

    expect($category->refresh()->is_active)->toBeFalse();
});

test('admin can delete a category without children', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.index'));

    $this->assertSoftDeleted($category);
});

test('deleting a parent category with children is blocked', function () {
    $parent = Category::factory()->create();
    Category::factory()->childOf($parent)->create();

    $response = $this->actingAs(admin())->delete(route('admin.categories.destroy', $parent));

    $response->assertRedirect(route('admin.categories.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('categories', [
        'id' => $parent->id,
        'deleted_at' => null,
    ]);
});

test('category tree renders arbitrarily deep nesting', function () {
    $level1 = Category::factory()->create(['name' => 'Level 1']);
    $level2 = Category::factory()->childOf($level1)->create(['name' => 'Level 2']);
    $level3 = Category::factory()->childOf($level2)->create(['name' => 'Level 3']);
    $level4 = Category::factory()->childOf($level3)->create(['name' => 'Level 4']);

    $response = $this->actingAs(admin())->get(route('admin.categories.index'));

    $response->assertStatus(200);
    $response->assertSeeInOrder(['Level 1', 'Level 2', 'Level 3', 'Level 4']);
});

test('a category cannot be assigned as its own descendant\'s parent', function () {
    $grandparent = Category::factory()->create();
    $parent = Category::factory()->childOf($grandparent)->create();
    $child = Category::factory()->childOf($parent)->create();

    // Attempt to make grandparent a child of its own grandchild.
    $response = $this->actingAs(admin())->put(route('admin.categories.update', $grandparent), [
        'name' => $grandparent->name,
        'parent_id' => $child->id,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('parent_id');
});

test('deleting a deeply nested parent is blocked even if only grandchildren exist', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->childOf($parent)->create();
    Category::factory()->childOf($child)->create();

    $response = $this->actingAs(admin())->delete(route('admin.categories.destroy', $parent));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
});
