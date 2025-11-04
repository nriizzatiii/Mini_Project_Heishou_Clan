<?php
include 'components/connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  
  <style>
    body {
      background-color: #1a1a1a;
      color: #fff;
    }
    
    /* Reviews Section Styles */
    .reviews-section {
      padding: 80px 0;
      background-color: #1a1a1a;
    }
    
    .reviews-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }
    
    .section-header h2 {
      font-size: 2.5rem;
      color: #fff;
      margin-bottom: 15px;
    }
    
    .section-divider {
      width: 80px;
      height: 3px;
      background: linear-gradient(to right, #ff3333, #cc0000);
      margin: 0 auto;
    }
    
    .reviews-carousel {
      position: relative;
      overflow: hidden;
      padding: 20px 0;
    }
    
    .reviews-track {
      display: flex;
      transition: transform 0.5s ease;
    }
    
    .review-card {
      min-width: 300px;
      margin: 0 15px;
      background: #2a2a2a;
      border: 1px solid #3a3a3a;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      padding: 30px;
      position: relative;
    }
    
    .review-card::before {
      content: '"';
      position: absolute;
      top: 10px;
      left: 20px;
      font-size: 60px;
      color: #444;
      font-family: serif;
      line-height: 1;
    }
    
    .review-content {
      margin-bottom: 20px;
      font-style: italic;
      color: #ccc;
      position: relative;
      z-index: 1;
      line-height: 1.6;
    }
    
    .review-author {
      display: flex;
      align-items: center;
    }
    
    .author-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      margin-right: 15px;
      object-fit: cover;
      border: 2px solid #444;
    }
    
    .author-info h4 {
      margin: 0;
      font-size: 1.1rem;
      color: #fff;
    }
    
    .rating {
      color: #ff3333;
      margin-top: 5px;
    }
    
    .carousel-controls {
      display: flex;
      justify-content: center;
      margin-top: 30px;
    }
    
    .carousel-btn {
      background: #333;
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin: 0 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .carousel-btn:hover {
      background: #ff3333;
    }
    
    .carousel-btn:disabled {
      background: #222;
      cursor: not-allowed;
      opacity: 0.5;
    }
    
    .carousel-indicators {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }
    
    .indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #555;
      margin: 0 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .indicator.active {
      background: #ff3333;
      width: 25px;
      border-radius: 5px;
    }
    
    @media (max-width: 768px) {
      .review-card {
        min-width: 250px;
      }
      
      .section-header h2 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>

  <!-- Customer Reviews Section -->
  <section id="review" class="review">
  <section class="reviews-section">
    <div class="reviews-container">
      <div class="section-header">
        <h2>What Our Customers Say</h2>
        <div class="section-divider"></div>
      </div>
      
      <div class="reviews-carousel">
        <div class="reviews-track" id="reviewsTrack">
          <!-- Review 1 -->
          <div class="review-card">
            <p class="review-content">The best Japanese restaurant in town! The ramen is absolutely authentic and the service is impeccable. I've been coming here for years and it never disappoints.</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person1/50/50.jpg" alt="Sarah Johnson" class="author-avatar">
              <div class="author-info">
                <h4>Sarah Johnson</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review 2 -->
          <div class="review-card">
            <p class="review-content">Amazing sushi! So fresh and beautifully presented. The chef clearly knows what they're doing. The atmosphere is also very relaxing and authentic.</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person2/50/50.jpg" alt="Michael Chen" class="author-avatar">
              <div class="author-info">
                <h4>Michael Chen</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review 3 -->
          <div class="review-card">
            <p class="review-content">The tempura is light and crispy, never greasy. The dipping sauces are perfectly balanced. This place is a hidden gem that deserves more recognition!</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person3/50/50.jpg" alt="Emily Rodriguez" class="author-avatar">
              <div class="author-info">
                <h4>Emily Rodriguez</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review 4 -->
          <div class="review-card">
            <p class="review-content">I brought my family here for my birthday dinner and everyone loved it! The kids enjoyed the chicken teriyaki and my husband couldn't stop talking about the tonkatsu.</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person4/50/50.jpg" alt="David Kim" class="author-avatar">
              <div class="author-info">
                <h4>David Kim</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review 5 -->
          <div class="review-card">
            <p class="review-content">As someone who lived in Japan for 5 years, I can say this is the most authentic Japanese food I've had since returning. The miso soup tastes just like what I used to have in Tokyo!</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person5/50/50.jpg" alt="Lisa Thompson" class="author-avatar">
              <div class="author-info">
                <h4>Lisa Thompson</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review 6 -->
          <div class="review-card">
            <p class="review-content">Great value for money! The portions are generous and the quality is consistently high. The lunch specials are particularly good value. Highly recommend!</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person6/50/50.jpg" alt="James Wilson" class="author-avatar">
              <div class="author-info">
                <h4>James Wilson</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review 7 -->
          <div class="review-card">
            <p class="review-content">The spicy ramen is to die for! Perfect level of heat and the broth is so flavorful. I've tried many ramen places but this one is definitely my favorite.</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person7/50/50.jpg" alt="Amanda Foster" class="author-avatar">
              <div class="author-info">
                <h4>Amanda Foster</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review 8 -->
          <div class="review-card">
            <p class="review-content">Wonderful dining experience! The staff is attentive and knowledgeable about the menu. They even made recommendations based on our preferences which were spot on.</p>
            <div class="review-author">
              <img src="https://picsum.photos/seed/person8/50/50.jpg" alt="Robert Martinez" class="author-avatar">
              <div class="author-info">
                <h4>Robert Martinez</h4>
                <div class="rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="carousel-controls">
        <button class="carousel-btn" id="prevBtn">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="carousel-btn" id="nextBtn">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      
      <div class="carousel-indicators" id="indicators"></div>
    </div>
  </section>
  </section>

  <?php include 'contact.php'?>

  <script>
    // Reviews Carousel Functionality
    document.addEventListener('DOMContentLoaded', function() {
      const reviewsTrack = document.getElementById('reviewsTrack');
      const reviewCards = document.querySelectorAll('.review-card');
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const indicatorsContainer = document.getElementById('indicators');
      
      let currentIndex = 0;
      const cardWidth = 330; // card width + margin
      const visibleCards = Math.floor(reviewsTrack.parentElement.offsetWidth / cardWidth);
      const maxIndex = Math.max(0, reviewCards.length - visibleCards);
      
      // Create indicators
      for (let i = 0; i <= maxIndex; i++) {
        const indicator = document.createElement('div');
        indicator.classList.add('indicator');
        if (i === 0) indicator.classList.add('active');
        indicator.addEventListener('click', () => goToSlide(i));
        indicatorsContainer.appendChild(indicator);
      }
      
      const indicators = document.querySelectorAll('.indicator');
      
      function updateCarousel() {
        // Update track position
        reviewsTrack.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
        
        // Update indicators
        indicators.forEach((indicator, index) => {
          indicator.classList.toggle('active', index === currentIndex);
        });
        
        // Update button states
        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex === maxIndex;
      }
      
      function goToSlide(index) {
        currentIndex = Math.max(0, Math.min(index, maxIndex));
        updateCarousel();
      }
      
      function nextSlide() {
        goToSlide(currentIndex + 1);
      }
      
      function prevSlide() {
        goToSlide(currentIndex - 1);
      }
      
      // Event listeners
      nextBtn.addEventListener('click', nextSlide);
      prevBtn.addEventListener('click', prevSlide);
      
      // Handle window resize
      window.addEventListener('resize', () => {
        const newVisibleCards = Math.floor(reviewsTrack.parentElement.offsetWidth / cardWidth);
        const newMaxIndex = Math.max(0, reviewCards.length - newVisibleCards);
        
        if (newMaxIndex !== maxIndex) {
          // Recreate indicators if needed
          indicatorsContainer.innerHTML = '';
          for (let i = 0; i <= newMaxIndex; i++) {
            const indicator = document.createElement('div');
            indicator.classList.add('indicator');
            if (i === 0) indicator.classList.add('active');
            indicator.addEventListener('click', () => goToSlide(i));
            indicatorsContainer.appendChild(indicator);
          }
        }
        
        // Adjust current index if needed
        if (currentIndex > newMaxIndex) {
          currentIndex = newMaxIndex;
        }
        
        updateCarousel();
      });
      
      // Auto-play carousel (optional)
      let autoplayInterval = setInterval(nextSlide, 5000);
      
      // Pause autoplay on hover
      reviewsTrack.addEventListener('mouseenter', () => {
        clearInterval(autoplayInterval);
      });
      
      reviewsTrack.addEventListener('mouseleave', () => {
        autoplayInterval = setInterval(nextSlide, 5000);
      });
      
      // Initialize carousel
      updateCarousel();
    });
  </script>
</body>
</html>