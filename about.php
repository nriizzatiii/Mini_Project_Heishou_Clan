<?php
include 'components/connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

 <section id="about" class="about">
    <div class="container">
      <div class="section-header">
        <h2>About <span>Us</span></h2>
        <div class="section-divider"></div>
      </div>
      <div class="about-content">
        <p class="about-text">
          At Heishou, we honor the art of Japanese cuisine — from the warmth of Ramen to the precision of Sushi. 
          Founded with passion and perfection, we serve each dish as a story, bringing Japan closer to your heart.
        </p>
        <div class="features-grid">
          <div class="feature-item">
            <div class="feature-icon">🍜</div>
            <h3>Authentic Flavors</h3>
            <p>Traditional recipes passed down through generations</p>
          </div>
          <div class="feature-item">
            <div class="feature-icon">👨‍🍳</div>
            <h3>Expert Chefs</h3>
            <p>Skilled artisans dedicated to culinary excellence</p>
          </div>
          <div class="feature-item">
            <div class="feature-icon">🌟</div>
            <h3>Premium Quality</h3>
            <p>Only the finest ingredients for exceptional taste</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>
<?php include 'review.php'?>
</html>