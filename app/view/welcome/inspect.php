<h1><?=noop::get( 'var/title' )?></h1>

<p>Calling <code>noop::start()</code> populate the noop registry with all the details of the request. It's important to check the current state of your app during its development.</p>
<p>You can view parts of this registry anywhere in your code, for example with <code>noop::inspect( 'app' )</code><p>

<?=noop::inspect( 'app' )?>

<p>More precisely with a <code>noop::inspect( 'request' )</code></p>

<?=noop::inspect( 'request' )?>

<p>Even more precisely with a path, like <code>noop::inspect( 'config/path/controller' )</code></p>

<?=noop::inspect( 'config/path/controller' )?>

<p>Or without any path to simply view all the registry, <code>noop::inspect()</code></p>

<?=noop::inspect()?>

<p>See a more <a href="help#registry-system-noop-">detailed overview of the registry</a> in help page.</p>