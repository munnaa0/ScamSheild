<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $priority = $_POST['priority'];
    $message = $_POST['message'];
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    $sql = "INSERT INTO contact_messages (
        first_name,
        last_name,
        email,
        phone,
        subject,
        priority,
        message,
        newsletter_subscription,
        status
    ) VALUES (
        '$first_name',
        '$last_name',
        '$email',
        '$phone',
        '$subject',
        '$priority',
        '$message',
        '$newsletter',
        'new'
    )";
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Thank you for contacting us! Your message has been sent successfully. We'll get back to you soon.";
        
        if ($newsletter == 1) {
            $name = $first_name . ' ' . $last_name;
            $newsletter_sql = "INSERT INTO notifications (email, name) 
                              VALUES ('$email', '$name')
                              ON DUPLICATE KEY UPDATE name = '$name'";
            mysqli_query($conn, $newsletter_sql);
        }
    } else {
        $error_message = "Sorry, there was an error sending your message. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us - ScamShield</title>
    <link rel="stylesheet" href="../models/css/contact.css" />
  </head>
  <body>
    <header>
      <nav class="navbar">
        <div class="nav-container">
          <div class="logo">
            <h1>ScamShield</h1>
            <span class="tagline">Protecting You from Fraud</span>
          </div>
          <ul class="nav-menu">
            <li><a href="../views/home.php">Home</a></li>
            <li><a href="../views/about.php">About</a></li>
            <li><a href="report.php">Report Scam</a></li>
            <li><a href="../views/database.php">Scam Database</a></li>
            <li><a href="../views/awareness.php">Awareness</a></li>
            <li><a href="contact.php" class="active">Contact</a></li>
            <?php if (is_logged_in()): ?>
              <li><a href="logout.php" class="login-btn">Logout</a></li>
            <?php else: ?>
              <li><a href="login.php" class="login-btn">Login</a></li>
            <?php endif; ?>
            <li><a href="../views/profile.php" class="profile-link">
              <div class="profile-icon">
                <div class="profile-circle">👤</div>
                <span class="profile-text"><?php echo $display_name; ?></span>
              </div>
            </a></li>
          </ul>
          <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <!-- Hero Section -->
      <section class="hero">
        <div class="container">
          <div class="hero-content">
            <h1>Get in Touch</h1>
            <p>
              Have questions, feedback, or need assistance? We're here to help.
              Reach out to our team anytime.
            </p>
          </div>
        </div>
      </section>

      <!-- Contact Form Section -->
      <section class="contact-section">
        <div class="container">
          <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-container">
              <div class="form-header">
                <h2>Send Us a Message</h2>
                <p>
                  Fill out the form below and we'll get back to you as soon as
                  possible.
                </p>
              </div>

              <form class="contact-form" id="contactForm" method="POST">
                
                <?php if ($success_message): ?>
                  <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $success_message; ?>
                  </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                  <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                  </div>
                <?php endif; ?>
                
                <div class="form-row">
                  <div class="form-group">
                    <label for="firstName">First Name *</label>
                    <input
                      type="text"
                      id="firstName"
                      name="firstName"
                      required
                      placeholder="Enter your first name"
                    />
                  </div>
                  <div class="form-group">
                    <label for="lastName">Last Name *</label>
                    <input
                      type="text"
                      id="lastName"
                      name="lastName"
                      required
                      placeholder="Enter your last name"
                    />
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input
                      type="email"
                      id="email"
                      name="email"
                      required
                      placeholder="Enter your email address"
                    />
                  </div>
                  <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input
                      type="tel"
                      id="phone"
                      name="phone"
                      placeholder="Enter your phone number (optional)"
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label for="subject">Subject *</label>
                  <select id="subject" name="subject" required>
                    <option value="">Select a subject</option>
                    <option value="general-inquiry">General Inquiry</option>
                    <option value="report-issue">Report a Problem</option>
                    <option value="verification-question">
                      Verification Question
                    </option>
                    <option value="media-inquiry">Media Inquiry</option>
                    <option value="partnership">Partnership Opportunity</option>
                    <option value="technical-support">Technical Support</option>
                    <option value="feedback">Feedback & Suggestions</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="priority">Priority Level</label>
                  <select id="priority" name="priority">
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="message">Message *</label>
                  <textarea
                    id="message"
                    name="message"
                    rows="6"
                    required
                    placeholder="Please provide details about your inquiry, question, or feedback..."
                  ></textarea>
                </div>

                <div class="checkbox-group">
                  <input type="checkbox" id="newsletter" name="newsletter" />
                  <label for="newsletter"
                    >Subscribe to our newsletter for scam alerts and prevention
                    tips</label
                  >
                </div>

                <div class="checkbox-group">
                  <input type="checkbox" id="privacy" name="privacy" required />
                  <label for="privacy"
                    >I agree to the <a href="#">Privacy Policy</a> and
                    <a href="#">Terms of Service</a> *</label
                  >
                </div>

                <div class="form-submit">
                  <button type="submit" class="btn btn-primary">
                    Send Message
                  </button>
                  <p class="submit-note">
                    We typically respond within 24 hours during business days.
                  </p>
                </div>
              </form>
            </div>
      </section>
    </main>

    <footer>
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h3>ScamShield</h3>
            <p>
              Protecting communities from fraud through awareness and
              collaboration.
            </p>
          </div>
          <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
              <li><a href="../views/about.php">About</a></li>
              <li><a href="report.php">Report Scam</a></li>
              <li><a href="../views/awareness.php">Prevention Tips</a></li>
              <li><a href="contact.php">Contact</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Resources</h4>
            <ul>
              <li><a href="../views/database.php">Scam Database</a></li>
              <li><a href="../views/awareness.php">Awareness Center</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms of Service</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Contact Info</h4>
            <p>Email: Meownna@scamshield.com</p>
            <p>Phone:01862122416</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 ScamShield. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </body>
</html>
