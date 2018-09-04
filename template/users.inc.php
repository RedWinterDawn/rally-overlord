<?php foreach ($keys as $sid => $key): ?>
<?php if (isset($key['password'])): ?>
username <?php echo $sid ?> privilege 15 secret 5 <?php echo $key['password'], "\n" ?>
<?php endif; ?>
<?php endforeach; ?>
