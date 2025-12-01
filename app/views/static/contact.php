<?php
// app/views/static/contact.php
?>
<section class="static-page">
  <div class="static-page__inner">
    <h1 class="static-page__title">Contact Us</h1>
    <div class="static-page__bar"></div>

    <div class="contact-grid">
      <div class="contact-info">
        <div class="contact-card">
          <div class="contact-card__icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </div>
          <h3>Email</h3>
          <p>support@medibook.com</p>
          <p>info@medibook.com</p>
        </div>

        <div class="contact-card">
          <div class="contact-card__icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
          </div>
          <h3>Phone</h3>
          <p>+84 1 23 45 67 89</p>
          <p>Mon - Fri: 9:00 AM - 6:00 PM</p>
        </div>

        <div class="contact-card">
          <div class="contact-card__icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </div>
          <h3>Address</h3>
          <p>123 Health Street</p>
          <p>75001 Hanoi, Vietnam</p>
        </div>
      </div>

      <div class="contact-form-wrapper">
        <h2>Send us a message</h2>
        <form class="contact-form" method="POST" action="#">
          <div class="form-group">
            <label for="contact-name">Full Name</label>
            <input type="text" id="contact-name" name="name" placeholder="Your name" required>
          </div>

          <div class="form-group">
            <label for="contact-email">Email Address</label>
            <input type="email" id="contact-email" name="email" placeholder="your@email.com" required>
          </div>

          <div class="form-group">
            <label for="contact-subject">Subject</label>
            <input type="text" id="contact-subject" name="subject" placeholder="How can we help?">
          </div>

          <div class="form-group">
            <label for="contact-message">Message</label>
            <textarea id="contact-message" name="message" rows="5" placeholder="Your message..." required></textarea>
          </div>

          <button type="submit" class="btn btn--primary btn--xl">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>
