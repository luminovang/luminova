<!DOCTYPE html>
<html>
<?php $this->begin('head'); ?>
  <head>
    <link rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="script.js"></script>
    <title>Website Title</title>
      <?php $this->begin('meta'); ?>
        <meta charset="utf-8">
      <?php $this->end(); ?>
  </head>
<?php $this->end(); ?>
    <body>
      <?php $this->begin('section'); ?>
        <section>
            <p>Website Page Section</p>
            <?php echo $this->import('card')->extend();?>
        </section>
      <?php $this->end(); ?>
      <?php $this->begin('footer'); ?>
        <footer>
            <p>&copy; Copyright 2023-2024 <a href="https://luminova.ng">Luminova</a></p>
            <?php $this->begin('social'); ?>
              <ul>
                <li><a href="#">FB</a></li>
                <li><a href="#">IG</a></li>
              </ul>
            <?php $this->end(); ?>
        </footer>
      <?php $this->end(); ?>
    </body>
</html>