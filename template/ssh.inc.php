ip ssh pubkey-chain
<?php foreach ($keys as $sid => $key): ?>
  username <?php echo $sid, "\n" ?>
   key-hash <?php echo implode(' ', array($key['type'], $key['hash'], $key['comment'])), "\n" ?>
<?php endforeach; ?>

