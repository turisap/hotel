<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>
<body>
    <h1>Welcome <?php echo $name; ?></h1>
    <p>Hello from the view!</p>
<ul>
    <?php foreach($colors as $color) : ?>
        <li><?php echo $color; ?></li>
    <?php endforeach; ?>
</ul>
</body>
</html>
