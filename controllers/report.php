<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reporter_name = $_POST['reporterName'];
    $reporter_email = $_POST['reporterEmail'];
    $scam_title = $_POST['scamTitle'];
    $scam_category = $_POST['scamCategory'];
    $scam_description = $_POST['scamDescription'];
    $date_occurred = $_POST['dateOccurred'];
    $amount_lost = $_POST['amountLost'];
    $scammer_email = $_POST['scammerEmail'];
    $scammer_phone = $_POST['scammerPhone'];
    $scammer_website = $_POST['scammerWebsite'];
    $additional_contacts = $_POST['additionalContacts'];
    $additional_evidence = $_POST['additionalEvidence'];
    $your_location = $_POST['yourLocation'];
    $scammer_location = $_POST['scammerLocation'];
    $reported_elsewhere = $_POST['reportedElsewhere'];
    $additional_notes = $_POST['additionalNotes'];
    $make_anonymous = isset($_POST['anonymize']) ? 1 : 0;
    $consent_given = isset($_POST['consent']) ? 1 : 0;
    
    $category_id = 0;
    $category_query = "SELECT category_id FROM scam_categories WHERE category_slug = '$scam_category'";
    $category_result = mysqli_query($conn, $category_query);
    
    if ($category_result && mysqli_num_rows($category_result) > 0) {
        $category_row = mysqli_fetch_assoc($category_result);
        $category_id = $category_row['category_id'];
    }
    
    $reporter_user_id = null;
    if (is_logged_in()) {
        $reporter_user_id = $_SESSION['user_id'];
    }
    
    $sql = "INSERT INTO scam_reports (
        reporter_user_id,
        reporter_name,
        reporter_email,
        scam_title,
        category_id,
        scam_description,
        date_occurred,
        amount_lost,
        scammer_email,
        scammer_phone,
        scammer_website,
        additional_contacts,
        evidence_description,
        reporter_location,
        scammer_location,
        reported_elsewhere,
        additional_notes,
        make_anonymous,
        consent_given,
        status
    ) VALUES (
        " . ($reporter_user_id ? "'$reporter_user_id'" : "NULL") . ",
        '$reporter_name',
        '$reporter_email',
        '$scam_title',
        '$category_id',
        '$scam_description',
        " . ($date_occurred ? "'$date_occurred'" : "NULL") . ",
        " . ($amount_lost ? "'$amount_lost'" : "0.00") . ",
        '$scammer_email',
        '$scammer_phone',
        '$scammer_website',
        '$additional_contacts',
        '$additional_evidence',
        '$your_location',
        '$scammer_location',
        " . ($reported_elsewhere ? "'$reported_elsewhere'" : "NULL") . ",
        '$additional_notes',
        '$make_anonymous',
        '$consent_given',
        'pending'
    )";
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Thank you! Your scam report has been submitted successfully. Our team will review it shortly.";
    } else {
        $error_message = "Sorry, there was an error submitting your report. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Report a Scam - ScamShield</title>
    <link rel="stylesheet" href="../models/css/report.css" />
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
            <li><a href="report.php" class="active">Report Scam</a></li>
            <li><a href="../views/database.php">Scam Database</a></li>
            <li><a href="../views/awareness.php">Awareness</a></li>
            <li><a href="contact.php">Contact</a></li>
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
            <h1>Report a Scam</h1>
            <p>
              Help protect others by reporting fraudulent activities. Your
              report will be verified and added to our database to warn the
              community.
            </p>
          </div>
        </div>
      </section>

      <!-- Report Form Section -->
      <section class="report-form-section">
        <div class="container">
          <div class="form-container">
            <div class="form-header">
              <h2>Submit Your Scam Report</h2>
              <p>
                Please provide as much detail as possible to help us verify and
                categorize your report effectively.
              </p>
            </div>

            <form class="report-form" id="scamReportForm" method="POST">
              
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
              
              <!-- Personal Information (Optional) -->
              <div class="form-section">
                <h3>Reporter Information (Optional)</h3>
                <div class="form-row">
                  <div class="form-group">
                    <label for="reporterName">Your Name</label>
                    <input
                      type="text"
                      id="reporterName"
                      name="reporterName"
                      placeholder="Enter your name (optional)"
                    />
                  </div>
                  <div class="form-group">
                    <label for="reporterEmail">Your Email</label>
                    <input
                      type="email"
                      id="reporterEmail"
                      name="reporterEmail"
                      placeholder="Enter your email (optional)"
                    />
                    <small
                      >We may contact you for additional information if
                      needed.</small
                    >
                  </div>
                </div>
              </div>

              <!-- Scam Details -->
              <div class="form-section">
                <h3>Scam Details</h3>

                <div class="form-group">
                  <label for="scamTitle">Scam Title *</label>
                  <input
                    type="text"
                    id="scamTitle"
                    name="scamTitle"
                    required
                    placeholder="Brief description of the scam"
                  />
                </div>

                <div class="form-group">
                  <label for="scamCategory">Category *</label>
                  <select id="scamCategory" name="scamCategory" required>
                    <option value="">Select a category</option>
                    <option value="phishing">Phishing/Email Scams</option>
                    <option value="financial">
                      Financial/Investment Scams
                    </option>
                    <option value="romance">Romance Scams</option>
                    <option value="tech-support">Tech Support Scams</option>
                    <option value="online-shopping">
                      Online Shopping Scams
                    </option>
                    <option value="cryptocurrency">Cryptocurrency Scams</option>
                    <option value="employment">Employment Scams</option>
                    <option value="social-media">Social Media Scams</option>
                    <option value="phone">Phone/SMS Scams</option>
                    <option value="identity-theft">Identity Theft</option>
                    <option value="charity">Charity Scams</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="scamDescription">Detailed Description *</label>
                  <textarea
                    id="scamDescription"
                    name="scamDescription"
                    rows="6"
                    required
                    placeholder="Describe what happened, how the scammer contacted you, what they asked for, and any other relevant details..."
                  ></textarea>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="dateOccurred">Date Occurred</label>
                    <input type="date" id="dateOccurred" name="dateOccurred" />
                  </div>
                  <div class="form-group">
                    <label for="amountLost">Amount Lost (if any)</label>
                    <input
                      type="number"
                      id="amountLost"
                      name="amountLost"
                      placeholder="0.00"
                      step="0.01"
                      min="0"
                    />
                  </div>
                </div>
              </div>

              <!-- Contact Information -->
              <div class="form-section">
                <h3>Scammer Contact Information</h3>

                <div class="form-row">
                  <div class="form-group">
                    <label for="scammerEmail">Scammer's Email</label>
                    <input
                      type="email"
                      id="scammerEmail"
                      name="scammerEmail"
                      placeholder="atif@scammer.com"
                    />
                  </div>
                  <div class="form-group">
                    <label for="scammerPhone">Scammer's Phone</label>
                    <input
                      type="tel"
                      id="scammerPhone"
                      name="scammerPhone"
                      placeholder="+88 0123 456 789"
                      maxlength="11"
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label for="scammerWebsite">Page/Website/URL Involved</label>
                  <input
                    type="text"
                    id="scammerWebsite"
                    name="scammerWebsite"
                    placeholder="https://iamascammer.com"
                  />
                </div>

                <div class="form-group">
                  <label for="additionalContacts"
                    >Additional Contact Methods</label
                  >
                  <textarea
                    id="additionalContacts"
                    name="additionalContacts"
                    rows="3"
                    placeholder="Social media profiles, messaging apps, or other contact methods used by the scammer..."
                  ></textarea>
                </div>
              </div>

              <!-- Evidence Upload -->
              <div class="form-section">
                <h3>Evidence</h3>

                <div class="form-group">
                  <label for="scamImages">Upload Images/Screenshots</label>
                  <input
                    type="file"
                    id="scamImages"
                    name="scamImages"
                    multiple
                    accept="image/*"
                  />
                  <small
                    >Upload screenshots of emails, messages, websites, or any
                    other visual evidence. Multiple files allowed.</small
                  >
                </div>

                <div class="form-group">
                  <label for="additionalEvidence"
                    >Additional Evidence Description</label
                  >
                  <textarea
                    id="additionalEvidence"
                    name="additionalEvidence"
                    rows="4"
                    placeholder="Describe any additional evidence you have (documents, recordings, etc.) that supports your report..."
                  ></textarea>
                </div>
              </div>

              <!-- Location Information -->
              <div class="form-section">
                <h3>Location Information</h3>

                <div class="form-row">
                  <div class="form-group">
                    <label for="yourLocation"
                      >Your Location (City, Country)</label
                    >
                    <input
                      type="text"
                      id="yourLocation"
                      name="yourLocation"
                      placeholder="Saidpur, Nilphamari"
                    />
                  </div>
                  <div class="form-group">
                    <label for="scammerLocation"
                      >Suspected Scammer Location</label
                    >
                    <input
                      type="text"
                      id="scammerLocation"
                      name="scammerLocation"
                      placeholder="If known, enter suspected location"
                    />
                  </div>
                </div>
              </div>

              <!-- Additional Information -->
              <div class="form-section">
                <h3>Additional Information</h3>

                <div class="form-group">
                  <label for="reportedElsewhere"
                    >Have you reported this elsewhere?</label
                  >
                  <select id="reportedElsewhere" name="reportedElsewhere">
                    <option value="">Select an option</option>
                    <option value="police">Local Police</option>
                    <option value="fbi">FBI/Federal Authorities</option>
                    <option value="ftc">FTC (Federal Trade Commission)</option>
                    <option value="bank">Bank/Financial Institution</option>
                    <option value="platform">
                      Online Platform (Facebook, etc.)
                    </option>
                    <option value="multiple">Multiple Agencies</option>
                    <option value="none">No, this is my first report</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="additionalNotes">Additional Notes</label>
                  <textarea
                    id="additionalNotes"
                    name="additionalNotes"
                    rows="4"
                    placeholder="Any additional information that might be helpful for our verification team..."
                  ></textarea>
                </div>
              </div>

              <!-- Consent and Verification -->
              <div class="form-section">
                <div class="checkbox-group">
                  <input type="checkbox" id="consent" name="consent" required />
                  <label for="consent"
                    >I confirm that the information provided is accurate to the
                    best of my knowledge and I consent to ScamShield using this
                    information to warn others about this scam. *</label
                  >
                </div>

                <div class="checkbox-group">
                  <input type="checkbox" id="anonymize" name="anonymize" />
                  <label for="anonymize"
                    >Make my report anonymous (remove any personal identifying
                    information before publishing)</label
                  >
                </div>
              </div>

              <!-- Submit Button -->
              <div class="form-submit">
                <button type="submit" class="btn btn-primary">
                  Submit Scam Report
                </button>
                <p class="submit-note">
                  Your report will be reviewed by our verification team and will be updated.
                </p>
              </div>
            </form>
          </div>

          <!-- Sidebar with Tips -->
          <div class="tips-sidebar">
            <div class="tips-card">
              <h3>Reporting Tips</h3>
              <ul>
                <li>
                  <strong>Be Detailed:</strong> The more information you
                  provide, the better we can help others avoid this scam.
                </li>
                <li>
                  <strong>Include Evidence:</strong> Screenshots, emails, and
                  URLs are very helpful for verification.
                </li>
                <li>
                  <strong>Stay Safe:</strong> Never provide additional personal
                  information to suspected scammers.
                </li>
                <li>
                  <strong>Act Quickly:</strong> Report scams as soon as possible
                  to help prevent others from falling victim.
                </li>
              </ul>
            </div>

            <div class="tips-card">
              <h3>What Happens Next?</h3>
              <ol>
                <li>Your report is submitted to our verification team</li>
                <li>We review and verify the information provided</li>
                <li>If verified, the scam is added to our public database</li>
                <li>Community members are warned about this scam type</li>
              </ol>
            </div>

            <div class="tips-card">
              <h3>Need Help?</h3>
              <p>
                If you need assistance filling out this form or have questions
                about the reporting process, please
                <a href="contact.php">contact us</a>.
              </p>

              <p>
                <strong>Emergency:</strong> If this involves immediate danger or
                ongoing financial fraud, please contact your local authorities
                first.
              </p>
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
