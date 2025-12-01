<?php
// app/views/static/help.php
?>
<section class="static-page">
  <div class="static-page__inner">
    <h1 class="static-page__title">Help Center</h1>
    <div class="static-page__bar"></div>

    <p class="static-page__intro">
      Welcome to the MediBook Help Center. Find answers to common questions and learn how to make the most of our platform.
    </p>

    <div class="help-search">
      <input type="text" id="help-search-input" placeholder="Search for help topics..." />
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </div>

    <div class="faq-section">
      <h2 class="faq-section__title">Frequently Asked Questions</h2>

      <div class="faq-category">
        <h3>Getting Started</h3>
        
        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>How do I create an account?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>Creating an account is easy! Click on the "Sign Up" button in the top right corner. You can register using your email address or through Google authentication. Fill in your personal information and you're ready to book appointments.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>Is MediBook free to use?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>Yes! MediBook is completely free for patients. You can search for doctors, view clinic information, and book appointments without any charges. The service fees are handled between you and your healthcare provider.</p>
          </div>
        </div>
      </div>

      <div class="faq-category">
        <h3>Booking Appointments</h3>
        
        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>How do I book an appointment?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>To book an appointment:</p>
            <ol>
              <li>Use the search bar to find a doctor by specialty, name, or location</li>
              <li>Browse the available clinics and doctors</li>
              <li>Select your preferred doctor and click on their profile</li>
              <li>Choose an available time slot that works for you</li>
              <li>Confirm your booking details and submit</li>
            </ol>
            <p>You'll receive a confirmation email with all the appointment details.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>Can I cancel or reschedule my appointment?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>Yes, you can manage your appointments from the "My Appointments" section in your dashboard. We recommend canceling or rescheduling at least 24 hours before your appointment to allow the clinic to offer the slot to other patients.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>Will I receive a reminder for my appointment?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>Yes! We send email reminders 24 hours before your scheduled appointment. Make sure your email address is up to date in your profile settings to receive these notifications.</p>
          </div>
        </div>
      </div>

      <div class="faq-category">
        <h3>Account & Profile</h3>
        
        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>How do I update my profile information?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>Go to your Profile page by clicking on your name in the top navigation and selecting "Profile". From there, you can update your personal information, contact details, and preferences.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>I forgot my password. What should I do?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>Click on "Login" and then "Forgot Password". Enter your email address and we'll send you instructions to reset your password. If you signed up using Google, simply use the "Continue with Google" option to log in.</p>
          </div>
        </div>
      </div>

      <div class="faq-category">
        <h3>For Healthcare Providers</h3>
        
        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>How can I register my clinic on MediBook?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>To register your clinic, create an account and select "Office/Clinic" as your role during registration. Once verified, you can set up your clinic profile, add doctors, and manage appointment schedules through your dashboard.</p>
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-item__question" aria-expanded="false">
            <span>How do I manage my clinic's schedule?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div class="faq-item__answer">
            <p>Access the "Doctor Schedule" section from your office dashboard. You can set available time slots, block off vacation days, and customize appointment durations for each doctor in your clinic.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="help-contact">
      <h2>Still need help?</h2>
      <p>Can't find what you're looking for? Our support team is here to help.</p>
      <a href="<?= BASE_URL ?>index.php?page=contact" class="btn btn--primary">Contact Support</a>
    </div>
  </div>
</section>

<script src="<?= BASE_URL ?>js/help.js"></script>
