<?php
include 'components/connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gallery</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
     <section id="gallery" class="gallery">
    <div class="container">
      <div class="section-header">
        <h2>Japanese Cuisine <span>Gallery</span></h2>
        <div class="section-divider"></div>
        <p class="section-subtitle">Discover the artistry and beauty of authentic Japanese dishes</p>
      </div>
      <div class="gallery-grid">
        <div class="gallery-item">
          <div class="gallery-image">
            <div class="cuisine-placeholder sushi-bg">
              <div class="cuisine-overlay">
                <h3>🍣 Premium Sushi</h3>
                <p>Fresh nigiri and sashimi selection</p>
              </div>
            </div>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-image">
            <div class="cuisine-placeholder ramen-bg">
              <div class="cuisine-overlay">
                <h3>🍜 Authentic Ramen</h3>
                <p>Rich tonkotsu and miso broths</p>
              </div>
            </div>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-image">
            <div class="cuisine-placeholder tempura-bg">
              <div class="cuisine-overlay">
                <h3>🍤 Crispy Tempura</h3>
                <p>Light and airy tempura perfection</p>
              </div>
            </div>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-image">
            <div class="cuisine-placeholder donburi-bg">
              <div class="cuisine-overlay">
                <h3>🍛 Hearty Donburi</h3>
                <p>Traditional rice bowls with toppings</p>
              </div>
            </div>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-image">
            <div class="cuisine-placeholder yakitori-bg">
              <div class="cuisine-overlay">
                <h3>🍗 Grilled Yakitori</h3>
                <p>Perfectly seasoned grilled skewers</p>
              </div>
            </div>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-image">
            <div class="cuisine-placeholder mochi-bg">
              <div class="cuisine-overlay">
                <h3>🍡 Sweet Mochi</h3>
                <p>Traditional Japanese desserts</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php include 'about.php'?>
</body>
</html>
