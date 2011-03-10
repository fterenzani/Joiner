# Joiner

## ... in five minutes

	$adapter = Joiner::setAdapter('default', 'sqlite::memory:');

	$schema = Joiner::getAdapter()->getSchema();
	$schema->setTable('prefix_users', 'User');
	$schema->setTable('prefix_pictures', 'Picture');

	$schema->setRelation('Picture.user_id', 'User.id');

	$users = Joiner::getAdapter()->getTable('User')
		->where('User.verified = ? AND User.id = ?', array('y', $_GET['id']))
		->join('Picture p')->addSelect('p.path AS user_picture')
		->groupBy('User.id');

	/* Will produce:
		SELECT prefix_users.*, p.path AS user_picture FROM prefix_users AS User
			INNER JOIN prefix_pictures AS p ON User.id = p.user_id
			WHERE User.verified = 'y' AND User.id = '1'
			GROUP BY User.id */

Now $users is an instance of Joiner_Table, but can be iterated or accessed like an array to connect and fetch the data from the database.

	if (count($users)) {
		echo $users[0]->name;
		foreach ($users as $user) {

			// Relations can be helpful in a row contest too:
			$user_pictures = $user->getRelated('Picture');

			/* Will produce:
				SELECT Picture.* FROM prefix_pictures AS Picture INNER JOIN
					prefix_users AS User ON User.id = Picture.user_id
					WHERE  User.id = '1' */

		}
	}


Another thing we can do is to add some methods to the records of any table. We just need to create a class with the same name of the table alias.

	class Picture extends Joiner_Model
	{
		function getImageTag() {
			return sprintf('<img src="%s" width="%s" height="%s" alt="%s" />'
				$this->path, $this->width, $this->height, $this->title);
		}
	}

	$picture = $adapter->getTable('Picture')->limit(1);
	echo $picture[0]->getImageTag();

Joiner don't provide a way to add methods to the table objects, but using static methods in the models is a nice workaround to me.

	class User extends Joiner_Model
	{
		static function addActiveQuery($table = NULL) {
			if (!$table) {
				$table = Joiner::getAdapter()->getTable('User u');
			}

			return $table->andWhere('u.verified = ?', 'y');
		}
	}

	// Now we can refactor the snippet above
	$users = User::addActiveQuery()->andWhere('u.id = ?', $_GET['id'])
		->join('Picture p')->addSelect('p.path AS user_picture')
		->groupBy('u.id');


Now let's imagine that the ralation within users and photos is many to many. We can just redefine the previous schema in this way:

	$schema = Joiner::getAdapter()->getSchema();

	$schema->setTable('prefix_users', 'User');
	$schema->setTable('prefix_pictures', 'Picture');
	$schema->setTable('prefix_user_picture', 'UserPicture');

	$schema->setRelation('UserPicture.user_id', 'User.id');
	$schema->setRelation('UserPicture.picture_id', 'Picture.id');

	$schema->setCrossReference('User', 'UserPicture', 'Picture');

That's all, we can execure all the examples above without any changes.

## Connections

## More about the Joiner_Adapter


## How to paginate results


## Logging

   // Get an adapter
   $adapter = Joiner::getAdapter();

   // Enable logging
   $adapter->enableLogging(true);

   // Do something to log
   $adapter->query('SELECT 1');

   // Dump the log in an HTML table:
   echo $adapter->getLog();

   // ... or get the log as array:
   foreach ($adapter->getLog()->toArray() as $log) {
       $function_call = $log[0];
       $arguments = $log[1];
       $time_spent_in_seconds = $log[2];
   }


