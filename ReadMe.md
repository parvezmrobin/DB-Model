# DB-Model
### Retrieves MySQL database instances SIMPLESTly in OOP style
<hr>

#### Initialize
Set the database name

```
\DbModel\Model::$database = 'database';
```
#### Retrieve All Instances
Get all the instances
```
$users = \DbModel\Model::all('users')
```
Now you can access all attributes of <kbd>User</kbd> in an Object oriented fashion.
```
$user = $users[0];
echo $user->name;
echo $user->email;
echo $users[2]->birth_day;
```
Do not want to retrieve all the fields? Just add an additional array or string to <kbd>all()</kbd> method to denote the fields you need.
```
$users = \DbModel\Model::all('users', 'name, email');
$users = \DbModel\Model::all('users', ['name', 'email']); // Same as previous
echo $users[0]->birth_day // Results error
```
#### Retrieve conditionally
Want to retrieve instances on condition? Use <kbd>where()</kbd> method and give the condition.
```
$adults = \DbModel\Model::where('users', 'age > 18');
$maleAdults = \DbModel\Model::where('users', 'age > 18 AND sex = "Male"');
$maleAdultNames = \DbModel\Model::where('users', 'age > 18 AND sex = "Male"', 'name');
```
#### Retrieve single instance
Want to retrieve a single user based on id? Use <kbd>find()</kbd> method to simplify the action.
```
$user = \DbModel\Model::find('users', '1');
$user = \DbModel\Model::find('users', 5);
```
<blockquote>
Note that <kbd>where()</kbd> and <kbd>all()</kbd> method returns array of instances where <kbd>find()</kbd> method returns a single instance.
</blockquote>

If your primary key is not name 'id', then you have to mention it.
```
$user = \DbModel\Model::find('users', '1', 'user_id');
```
And obviously the columns as the last parameter.
```
$user = \DbModel\Model::find('users', '1', 'user_id', 'first_name, last_name');

$columns = ['first_name', 'last_name'];
$user = \DbModel\Model::find('users', '1', 'user_id', $columns);
```
<hr>

### Retrieve using relation
<kbd>DB-Model</kbd> Supports <kbd>One to Many</kbd>, <kbd>Many to One</kbd> and <kbd>Many to Many</kbd> relationship. However, you can use these methods to retrieve your <kbd>One to One relation as well.
<blockquote>
<kbd>oneToMany()</kbd>, <kbd>manyToOne()</kbd> and <kbd>manytoMany()</kbd> are instance method. Where <kbd>all()</kbd>, <kbd>where()</kbd> and <kbd>find()</kbd> are static method.
</blockquote>

#### One to Many
Say, your <kbd>User</kbd> has many <kbd>Post</kbd>s. So, <kbd>Post</kbd>s must have a <kbd>user_id</kbd> field to store primary key of <kbd>User</kbd>. The <kbd>user_id</kbd> field in </kbd>Post is said <kbd>Foreign Key</kbd>. Again, <kbd>id</kbd> of <kbd>User</kbd> is said <kbd>Referenced Key</kbd> as it is referenced by <kbd>user_id</kbd> in <kbd>Post</kbd>.
You can easily retrieve the <kbd>Post</kbd>s of <kbd>User</kbd> using <kbd>oneToMany()</kbd> method. The simplest form is
```
$user = \DbModel\Model::find('users', '1');
$posts = $user->oneToMany('posts', 'user_id');
```
If primary key of <kbd>User</kbd> is not <kbd>id</kbd>, then you should guess what to pass next.
```
$user = \DbModel\Model::find('users', '1', 'user_primary_key');
$posts = $user->oneToMany('posts', 'user_id', 'user_primary_key');

// To retrieve title and body only
$posts = $user->oneToMany('posts', 'user_id', 'user_primary_key', 'title, body');
```
If you are clever enough, you should understand that, <kbd>oneToMany()</kbd> can also be used to store <kbd>one to one</kbd> relationship. If <kbd>Settings</kbd> has an <kbd>one to one</kbd> relationship with <kbd>User</kbd>, then each <kbd>User</kbd> will have exactly one <kbd>Settings</kbd>. So what are you waiting for?
```
$user = \DbModel\Model::find('users', '1');
$settings = $user->oneToMany('settings', 'user_id')[0]; //Retrieves the only settings user have.
```
Of course, you can use the rest of parameters al well.
#### Many to One
What if you need to get the corresponding <kbd>User</kbd> instance from a <kbd>Post</kbd> post instance? <kbd>manyToOne()</kbd> is here for you with same signature.
```
$post = \DbModel\Model::find('posts', '1');
$user = $post->manyToOne('user', 'user_id');
```
If your <kbd>User</kbd> has a different primary key than <kbd>id</kbd>, then mention that too.
```
$post = \DbModel\Model::find('posts', '1');
$user = $post->manyToOne('user', 'user_id', 'user_primary_key');

// To specify the columns

$user = $post->manyToOne('user', 'user_id', 'user_primary_key', 'name, email');
```
Again, if you want to retrieve using **inverse** <kbd>one to one</kbd> relationship, use <kbd>manyToOne()</kbd> method as well.
```
$settings = \DbModel\Model::find('settings', '1');
$user = $post->manyToOne('user', 'user_id', 'user_primary_key');
```
#### Many to Many
To implement <kbd>Many to Many</kbd> relationship in database you need a intermediate table which contains foreign key of both the related table. Suppose, <kbd>Post</kbd> has a many to many relationship with <kbd>Tag</kbd>. Then, you need an intermediate table, say <kbd>post_tag</kbd> which contains foreign key of <kbd>Post</kbd> and <kbd>Tag</kbd>, say <kbd>post_id</kbd> and <kbd>tag_id</kbd>. Now, your code will be
```
$post = \DbModel\Model::find('posts', '1');
$tags = $post->manytoMany('tags', 'post_tag', 'post_id', 'tag_id');

// Or inversely
$tag = \DbModel\Model::find('tags', '1');
$posts = $tag->manytoMany('posts', 'post_tag', 'tag_id', 'post_id');
```
If the models have different primary key than <kbd>id</kbd>, then mention it next.
```
$post = \DbModel\Model::find('posts', '1', 'post_primary_key');
$tags = $post->manytoMany('tags', 'post_tag', 'post_id', 'tag_id',
    'post_primary_key', 'tag_primary_key');

// Or inversely
$tag = \DbModel\Model::find('tags', '1', 'tag_primary_key');
$posts = $tag->manytoMany('posts', 'post_tag', 'tag_id', 'post_id',
    'tag_primary_key', 'post_primary_key');

// Select columns as well
$posts = $tag->manytoMany('posts', 'post_tag', 'tag_id', 'post_id',
    'tag_primary_key', 'post_primary_key', ['name', 'body']);
```
