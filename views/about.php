<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();

$total_reports = 0;
$total_query = "SELECT COUNT(*) as total FROM scam_reports";
$total_result = mysqli_query($conn, $total_query);
if ($total_result) {
    $total_row = mysqli_fetch_assoc($total_result);
    $total_reports = $total_row['total'];
}

$verified_reports = 0;
$verified_query = "SELECT COUNT(*) as verified FROM scam_reports WHERE status = 'verified'";
$verified_result = mysqli_query($conn, $verified_query);
if ($verified_result) {
    $verified_row = mysqli_fetch_assoc($verified_result);
    $verified_reports = $verified_row['verified'];
}

$protected_users = 0;
$users_query = "SELECT COUNT(DISTINCT reporter_user_id) as users FROM scam_reports WHERE reporter_user_id IS NOT NULL";
$users_result = mysqli_query($conn, $users_query);
if ($users_result) {
    $users_row = mysqli_fetch_assoc($users_result);
    $protected_users = $users_row['users'];
}

$anon_query = "SELECT COUNT(*) as anon FROM scam_reports WHERE reporter_user_id IS NULL";
$anon_result = mysqli_query($conn, $anon_query);
if ($anon_result) {
    $anon_row = mysqli_fetch_assoc($anon_result);
    $protected_users += $anon_row['anon'];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us - ScamShield</title>
    <link rel="stylesheet" href="../models/css/about.css" />
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
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php" class="active">About</a></li>
            <li><a href="../controllers/report.php">Report Scam</a></li>
            <li><a href="database.php">Scam Database</a></li>
            <li><a href="awareness.php">Awareness</a></li>
            <li><a href="../controllers/contact.php">Contact</a></li>
            <?php if (is_logged_in()): ?>
              <li><a href="../controllers/logout.php" class="login-btn">Logout</a></li>
            <?php else: ?>
              <li><a href="../controllers/login.php" class="login-btn">Login</a></li>
            <?php endif; ?>
            <li><a href="profile.php" class="profile-link">
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
            <h1>About ScamShield</h1>
            <p>
              Empowering communities to fight fraud through collaborative
              reporting, verified databases, and comprehensive awareness
              education.
            </p>
          </div>
        </div>
      </section>

      <!-- Mission Section -->
      <section class="mission">
        <div class="container">
          <div class="content-grid">
            <div class="mission-content">
              <h2>Our Mission</h2>
              <p>
                ScamShield is dedicated to creating a safer digital environment
                by providing a comprehensive platform for scam reporting,
                verification, and community awareness. We believe that through
                collective vigilance and shared knowledge, we can significantly
                reduce the impact of fraud on individuals and communities.
              </p>
              <div class="mission-stats">
                <div class="stat">
                  <h3><?php echo number_format($total_reports); ?>+</h3>
                  <p>Scams Reported</p>
                </div>
                <div class="stat">
                  <h3><?php echo number_format($verified_reports); ?>+</h3>
                  <p>Verified Reports</p>
                </div>
                <div class="stat">
                  <h3><?php echo number_format($protected_users); ?>+</h3>
                  <p>Protected Users</p>
                </div>
              </div>
            </div>
            <div class="mission-image">
              <img src="../images/logo.jpg" alt="ScamShield Mission" />
            </div>
          </div>
        </div>
      </section>

      <!-- Features Section -->
      <section class="features">
        <div class="container">
          <h2>How We Protect You</h2>
          <div class="features-grid">
            <div class="feature-card">
              <div class="feature-icon">🛡️</div>
              <h3>Comprehensive Reporting</h3>
              <p>
                Our detailed reporting system captures essential information
                about scams, including contact details, financial impact, and
                evidence documentation.
              </p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">✅</div>
              <h3>Verification Process</h3>
              <p>
                Every reported scam undergoes thorough verification by our team
                to ensure database accuracy and reliability.
              </p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">📚</div>
              <h3>Education & Awareness</h3>
              <p>
                Access to prevention tips, awareness articles, and up-to-date
                information about emerging fraud tactics.
              </p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">🔍</div>
              <h3>Searchable Database</h3>
              <p>
                Browse and search our comprehensive database of verified scam
                reports to stay informed about current threats.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Legal Documents Navigation -->
      <section class="legal-nav">
        <div class="container">
          <h2>Legal Information</h2>
          <div class="legal-buttons">
            <button class="btn btn-primary" onclick="showSection('privacy')">
              Privacy Policy
            </button>
            <button class="btn btn-secondary" onclick="showSection('terms')">
              Terms of Service
            </button>
            <button class="btn btn-outline" onclick="showSection('about')">
              Back to About
            </button>
          </div>
        </div>
      </section>

      <!-- Privacy Policy Section -->
      <section id="privacy-section" class="legal-section" style="display: none">
        <div class="container">
          <div class="legal-content">
            <h2>Privacy Policy</h2>
            <p class="last-updated">Last Updated: October 1, 2025</p>

            <div class="legal-item">
              <h3>1. Information We Collect</h3>
              <h4>1.1 Personal Information</h4>
              <p>
                When you use ScamShield, we may collect the following personal
                information:
              </p>
              <ul>
                <li>Name and contact information (email, phone number)</li>
                <li>Account credentials (username, encrypted password)</li>
                <li>Location information (for reporting and verification purposes)</li>
                <li>Scam report details and evidence files</li>
                <li>Communication preferences and newsletter subscriptions</li>
              </ul>

              <h4>1.2 Scam Report Information</h4>
              <p>When you submit a scam report, we collect:</p>
              <ul>
                <li>Details about the fraudulent activity</li>
                <li>Scammer contact information (emails, phone numbers, websites)</li>
                <li>Financial impact and transaction details</li>
                <li>Evidence files (screenshots, documents, etc.)</li>
                <li>Your contact information for verification purposes</li>
              </ul>

              <h4>1.3 Technical Information</h4>
              <p>We automatically collect certain technical information:</p>
              <ul>
                <li>IP address and browser information</li>
                <li>Device type and operating system</li>
                <li>Usage patterns and site interaction data</li>
                <li>Cookies and similar tracking technologies</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>2. How We Use Your Information</h3>
              <p>We use your information for the following purposes:</p>
              <ul>
                <li>
                  <strong>Scam Prevention:</strong> To verify and publish scam reports
                  in our database
                </li>
                <li>
                  <strong>User Services:</strong> To create and manage your account
                </li>
                <li>
                  <strong>Communication:</strong> To send newsletters, alerts, and
                  important updates
                </li>
                <li>
                  <strong>Legal Compliance:</strong> To comply with applicable laws
                  and regulations
                </li>
                <li>
                  <strong>Platform Improvement:</strong> To analyze usage and improve
                  our services
                </li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>3. Information Sharing and Disclosure</h3>
              <h4>3.1 Public Database</h4>
              <p>
                Verified scam reports are published in our public database with
                the following considerations:
              </p>
              <ul>
                <li>Personal identifying information is removed unless explicitly consented</li>
                <li>Users can choose to make reports anonymous</li>
                <li>Only verified information is published</li>
              </ul>

              <h4>3.2 Law Enforcement and Legal Authorities</h4>
              <p>
                We may share information with law enforcement agencies when:
              </p>
              <ul>
                <li>Required by law or legal process</li>
                <li>Necessary to protect public safety</li>
                <li>To assist in criminal investigations</li>
              </ul>

              <h4>3.3 Service Providers</h4>
              <p>
                We may share information with trusted third-party service
                providers who help us operate our platform, subject to
                confidentiality agreements.
              </p>
            </div>

            <div class="legal-item">
              <h3>4. Data Security</h3>
              <p>We implement comprehensive security measures:</p>
              <ul>
                <li>Encrypted data transmission and storage</li>
                <li>Access controls and authentication systems</li>
                <li>Regular security audits and monitoring</li>
                <li>Secure file upload and storage systems</li>
                <li>Password hashing and protection</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>5. Your Rights and Choices</h3>
              <p>You have the following rights regarding your personal information:</p>
              <ul>
                <li>
                  <strong>Access:</strong> Request access to your personal data
                </li>
                <li>
                  <strong>Correction:</strong> Request correction of inaccurate data
                </li>
                <li>
                  <strong>Deletion:</strong> Request deletion of your personal data
                  (subject to legal requirements)
                </li>
                <li>
                  <strong>Anonymization:</strong> Request to make your reports
                  anonymous
                </li>
                <li>
                  <strong>Opt-out:</strong> Unsubscribe from newsletters and
                  communications
                </li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>6. Data Retention</h3>
              <p>We retain your information as follows:</p>
              <ul>
                <li>Account information: Until account deletion is requested</li>
                <li>Scam reports: Indefinitely for public safety purposes</li>
                <li>Contact messages: 3 years for service improvement</li>
                <li>Technical logs: 12 months for security purposes</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>7. Children's Privacy</h3>
              <p>
                ScamShield is not intended for use by children under 13 years of
                age. We do not knowingly collect personal information from
                children under 13.
              </p>
            </div>

            <div class="legal-item">
              <h3>8. International Data Transfers</h3>
              <p>
                Your information may be transferred to and processed in countries
                other than your own. We ensure appropriate safeguards are in
                place to protect your data.
              </p>
            </div>

            <div class="legal-item">
              <h3>9. Changes to Privacy Policy</h3>
              <p>
                We may update this Privacy Policy periodically. We will notify
                users of significant changes via email or platform notification.
              </p>
            </div>

            <div class="legal-item">
              <h3>10. Contact Information</h3>
              <p>
                For privacy-related questions or requests, contact us at:
              </p>
              <ul>
                <li>Email: privacy@scamshield.com</li>
                <li>Phone: +1 (555) 123-4567</li>
                <li>Address: ScamShield Privacy Team</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- Terms of Service Section -->
      <section id="terms-section" class="legal-section" style="display: none">
        <div class="container">
          <div class="legal-content">
            <h2>Terms of Service</h2>
            <p class="last-updated">Last Updated: October 1, 2025</p>

            <div class="legal-item">
              <h3>1. Acceptance of Terms</h3>
              <p>
                By accessing and using ScamShield ("the Platform"), you agree to
                be bound by these Terms of Service. If you do not agree to these
                terms, please do not use our services.
              </p>
            </div>

            <div class="legal-item">
              <h3>2. Description of Service</h3>
              <p>
                ScamShield is an online platform that allows users to:
              </p>
              <ul>
                <li>Report fraudulent activities and scams</li>
                <li>Access a database of verified scam reports</li>
                <li>Receive fraud prevention education and awareness information</li>
                <li>Participate in community-driven fraud prevention efforts</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>3. User Eligibility</h3>
              <p>To use ScamShield, you must:</p>
              <ul>
                <li>Be at least 13 years of age</li>
                <li>Provide accurate and complete registration information</li>
                <li>Maintain the security of your account credentials</li>
                <li>Comply with all applicable laws and regulations</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>4. User Responsibilities</h3>
              <h4>4.1 Accurate Reporting</h4>
              <p>When submitting scam reports, you agree to:</p>
              <ul>
                <li>Provide truthful and accurate information</li>
                <li>Not submit false or misleading reports</li>
                <li>Include relevant evidence when available</li>
                <li>Respect the privacy of others</li>
              </ul>

              <h4>4.2 Prohibited Activities</h4>
              <p>You agree not to:</p>
              <ul>
                <li>Submit defamatory, false, or malicious content</li>
                <li>Use the platform for illegal activities</li>
                <li>Attempt to hack or disrupt the platform</li>
                <li>Share copyrighted material without permission</li>
                <li>Harass or threaten other users</li>
                <li>Create multiple accounts to circumvent restrictions</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>5. Content and Intellectual Property</h3>
              <h4>5.1 User Content</h4>
              <p>By submitting content to ScamShield, you:</p>
              <ul>
                <li>Retain ownership of your original content</li>
                <li>
                  Grant us a license to use, modify, and distribute your content
                  for platform purposes
                </li>
                <li>Warrant that you have the right to submit the content</li>
                <li>Agree that verified reports may be published publicly</li>
              </ul>

              <h4>5.2 Platform Content</h4>
              <p>ScamShield owns all platform-specific content, including:</p>
              <ul>
                <li>Website design and functionality</li>
                <li>Proprietary algorithms and verification processes</li>
                <li>Educational materials and awareness content</li>
                <li>Compiled database and analytics</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>6. Verification and Moderation</h3>
              <p>ScamShield reserves the right to:</p>
              <ul>
                <li>Verify submitted scam reports before publication</li>
                <li>Reject reports that don't meet our standards</li>
                <li>Remove content that violates these terms</li>
                <li>Suspend or terminate user accounts for violations</li>
                <li>Modify or update verification processes</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>7. Privacy and Data Protection</h3>
              <p>
                Your privacy is important to us. Please review our Privacy Policy
                to understand how we collect, use, and protect your information.
                Key points include:
              </p>
              <ul>
                <li>We collect information necessary for scam prevention</li>
                <li>Personal identifying information is protected</li>
                <li>You can choose to make reports anonymous</li>
                <li>We comply with applicable data protection laws</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>8. Disclaimers and Limitations</h3>
              <h4>8.1 Service Availability</h4>
              <p>
                ScamShield is provided "as is" and "as available." We do not
                guarantee:
              </p>
              <ul>
                <li>Continuous or uninterrupted service availability</li>
                <li>Complete accuracy of all database information</li>
                <li>Prevention of all fraudulent activities</li>
                <li>Compatibility with all devices and browsers</li>
              </ul>

              <h4>8.2 Limitation of Liability</h4>
              <p>
                ScamShield shall not be liable for:
              </p>
              <ul>
                <li>Direct, indirect, or consequential damages</li>
                <li>Financial losses resulting from scam activities</li>
                <li>User-generated content or third-party actions</li>
                <li>Data loss or security breaches beyond our control</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>9. Account Termination</h3>
              <p>
                We may suspend or terminate your account for:
              </p>
              <ul>
                <li>Violation of these Terms of Service</li>
                <li>Submission of false or misleading information</li>
                <li>Illegal or harmful activities</li>
                <li>Extended periods of inactivity</li>
              </ul>
              <p>
                You may terminate your account at any time by contacting us.
                Upon termination, your access will be revoked, but previously
                submitted and verified reports may remain in our database.
              </p>
            </div>

            <div class="legal-item">
              <h3>10. Third-Party Services</h3>
              <p>
                ScamShield may integrate with third-party services. We are not
                responsible for the privacy practices or content of third-party
                websites or services.
              </p>
            </div>

            <div class="legal-item">
              <h3>11. Governing Law</h3>
              <p>
                These Terms of Service are governed by and construed in
                accordance with applicable laws. Any disputes will be resolved
                through binding arbitration or appropriate legal channels.
              </p>
            </div>

            <div class="legal-item">
              <h3>12. Changes to Terms</h3>
              <p>
                We reserve the right to modify these Terms of Service at any
                time. Significant changes will be communicated to users via:
              </p>
              <ul>
                <li>Email notification to registered users</li>
                <li>Platform announcements and notifications</li>
                <li>Updated "Last Modified" date on this page</li>
              </ul>
              <p>
                Continued use of the platform after changes constitutes
                acceptance of the new terms.
              </p>
            </div>

            <div class="legal-item">
              <h3>13. Contact Information</h3>
              <p>
                For questions about these Terms of Service, contact us at:
              </p>
              <ul>
                <li>Email: Meownna@scamshield.com</li>
                <li>Phone: +880 18621 22416</li>
                <li>Address: ScamShield Legal Department</li>
              </ul>
            </div>

            <div class="legal-item">
              <h3>14. Severability</h3>
              <p>
                If any provision of these Terms of Service is found to be
                unenforceable, the remaining provisions will continue in full
                force and effect.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Team Section -->
      <section id="about-section" class="team">
        <div class="container">
          <h2>Our Commitment</h2>
          <div class="commitment-content">
            <p>
              ScamShield is committed to maintaining the highest standards of
              data protection, user privacy, and service reliability. Our team
              works continuously to improve the platform's effectiveness in
              combating fraud while ensuring user trust and security.
            </p>
            <div class="contact-cta">
              <h3>Get in Touch</h3>
              <p>
                Have questions about our platform, policies, or services?
                We're here to help.
              </p>
              <div class="cta-buttons">
                <a href="../controllers/contact.php" class="btn btn-primary">Contact Us</a>
                <a href="../controllers/report.php" class="btn btn-secondary">Report a Scam</a>
              </div>
            </div>
          </div>
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
              <li><a href="about.php">About</a></li>
              <li><a href="../controllers/report.php">Report Scam</a></li>
              <li><a href="awareness.php">Prevention Tips</a></li>
              <li><a href="../controllers/contact.php">Contact</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Resources</h4>
            <ul>
              <li><a href="database.php">Scam Database</a></li>
              <li><a href="awareness.php">Awareness Center</a></li>
              <li><a href="about.php" onclick="showSection('privacy')">Privacy Policy</a></li>
              <li><a href="about.php" onclick="showSection('terms')">Terms of Service</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Contact Info</h4>
            <p>Email: Meownna@scamshield.com</p>
            <p>Phone: +880 18621 22416</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 ScamShield. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <script>
      function showSection(section, scrollToTop = false) {
        document.getElementById('about-section').style.display = 'none';
        document.getElementById('privacy-section').style.display = 'none';
        document.getElementById('terms-section').style.display = 'none';

        if (section === 'privacy') {
          document.getElementById('privacy-section').style.display = 'block';
        } else if (section === 'terms') {
          document.getElementById('terms-section').style.display = 'block';
        } else {
          document.getElementById('about-section').style.display = 'block';
        }

        if (scrollToTop) {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');

        hamburger.addEventListener('click', function() {
          hamburger.classList.toggle('active');
          navMenu.classList.toggle('active');
        });

        document.querySelectorAll('.nav-menu a').forEach(link => {
          link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
          });
        });

        const sectionToShow = sessionStorage.getItem('aboutSection');
        if (sectionToShow) {
          showSection(sectionToShow, true);
          sessionStorage.removeItem('aboutSection');
        }
      });
    </script>
  </body>
</html>