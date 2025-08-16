<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$coach_id = $_SESSION['coach_id'];

// Verify coach owns the event
$check_event_query = "SELECT * FROM sports_events WHERE id = ? AND coach_id = ?";
$stmt = $conn->prepare($check_event_query);
$stmt->bind_param("ii", $event_id, $coach_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "You do not have permission to edit this event.";
    header("Location: coach1.php");
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $sport_name = $_POST['sport_name'];
    $description = $_POST['description'];
    $rewards = $_POST['rewards'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_group_required = isset($_POST['is_group_required']) ? 1 : 0;
    $women = isset($_POST['women']) ? 1 : 0;

    // Validate team event requirements
    $error_message = null;
    if ($is_group_required) {
        if (!isset($_POST['max_group_members']) || empty($_POST['max_group_members'])) {
            $error_message = "Maximum team members is required for team events.";
        } else {
            $max_group_members = intval($_POST['max_group_members']);
            if ($max_group_members < 2) {
                $error_message = "Team events must have at least 2 members.";
            }
        }
    }

    if (!$error_message) {
        $max_group_members = $is_group_required ? intval($_POST['max_group_members']) : NULL;

        $update_query = "UPDATE sports_events SET name = ?, sport_name = ?, description = ?, rewards = ?, start_date = ?, end_date = ?, is_group_required = ?, max_group_members = ?, women = ? WHERE id = ? AND coach_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssssiiiii", $name, $sport_name, $description, $rewards, $start_date, $end_date, $is_group_required, $max_group_members, $women, $event_id, $coach_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Event updated successfully!";
            header("Location: coach1.php");
            exit();
        } else {
            $error_message = "Error updating event: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4C3F91;
            --primary-light: #9145B6;
            --primary-dark: #2D2456;
            --secondary: #FFD700;
            --accent: #B1B7F2;
            --background: #f0f2f5;
            --text-dark: #2c3e50;
            --text-light: #95a5a6;
            --white: #ffffff;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.15);
            --border-radius: 16px;
            --border-radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --gradient: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f6f8fb 0%, #e9edf3 100%);
            min-height: 100vh;
            padding: 2rem;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 750px;
            margin: 2rem auto;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            border: 1px solid var(--glass-border);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-title {
            text-align: center;
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.2rem;
            margin-bottom: 1.2rem;
        }

        .form-group {
            margin-bottom: 0;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: var(--primary-dark);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background: var(--white);
            color: var(--text-dark);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        textarea.form-control {
            min-height: 80px;
        }

        .checkbox-container {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            padding: 0.8rem;
            margin-bottom: 1.2rem;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            border: 1px solid var(--glass-border);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: var(--white);
            border-radius: 6px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        #maxGroupContainer {
            max-width: 250px;
            margin-bottom: 1.2rem;
        }

        .submit-btn {
            max-width: 300px;
            margin: 1rem auto 0;
            padding: 0.8rem;
        }

        /* Enhanced visual feedback */
        .form-control:hover {
            border-color: var(--primary-light);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(76, 63, 145, 0.1);
        }

        /* Message styling improvements */
        .message {
            max-width: 90%;
            margin: 1rem auto;
            padding: 0.8rem;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .checkbox-container {
                grid-template-columns: 1fr;
            }

            #maxGroupContainer {
                max-width: 100%;
            }
        }

        .error-alert {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            display: none;
            animation: fadeIn 0.3s ease-out;
        }

        .required-field label::after {
            content: ' *';
            color: #e74c3c;
        }

        .logo-container {
            display: none;
        }

        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: var(--border-radius-sm);
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.3s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .message.success {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
            border: 1px solid rgba(46, 213, 115, 0.2);
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .message i {
            font-size: 1.2rem;
        }

        .modal-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .modal p {
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        /* Modal Styles */
        .modal {
            display: none;  /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            max-width: 400px;
            width: 90%;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translate(-50%, -50%) scale(1);
        }

        .modal-title {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .modal-btn {
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .confirm-btn {
            background: var(--gradient);
            color: var(--white);
            border: none;
        }

        .cancel-btn {
            background: var(--white);
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    <div class="container">
        <h2 class="page-title">Edit Event</h2>

        <?php if (isset($error_message)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="editEventForm">
            <div class="form-grid">
                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($event['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Sport Name</label>
                    <input type="text" name="sport_name" class="form-control" value="<?php echo htmlspecialchars($event['sport_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $event['start_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $event['end_date']; ?>" required>
                </div>

                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label>Rewards</label>
                    <textarea name="rewards" class="form-control"><?php echo htmlspecialchars($event['rewards']); ?></textarea>
                </div>
            </div>

            <div class="checkbox-container">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_group_required" name="is_group_required" <?php echo $event['is_group_required'] ? 'checked' : ''; ?> onclick="toggleGroupField()">
                    <label for="is_group_required">Team Event</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="women" name="women" <?php echo $event['women'] ? 'checked' : ''; ?>>
                    <label for="women">Women Only Event</label>
                </div>
            </div>

            <div class="form-group" id="maxGroupContainer">
                <label>Maximum Team Members</label>
                <input type="number" id="max_group_members" name="max_group_members" class="form-control" value="<?php echo $event['max_group_members']; ?>" min="1" <?php echo $event['is_group_required'] ? '' : 'disabled'; ?>>
            </div>

            <button type="button" class="submit-btn" onclick="showConfirmation()">
                <i class="fas fa-save"></i> Update Event
            </button>
        </form>
    </div>

    <!-- Enhanced Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <i class="fas fa-question-circle modal-icon"></i>
            <h3 class="modal-title">Confirm Update</h3>
            <p>Are you sure you want to update this event?</p>
            <div class="modal-buttons">
                <button class="modal-btn confirm-btn" onclick="submitFormAndRedirect()">
                    <i class="fas fa-check"></i> Yes, Update
                </button>
                <button class="modal-btn cancel-btn" onclick="hideConfirmation()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <div id="error-message" class="error-alert" style="display: none;"></div>

    <?php include('../includes/footer.php'); ?>

    <script>
        function validateForm() {
            var groupRequired = document.getElementById('is_group_required');
            var maxGroupField = document.getElementById('max_group_members');
            var errorDiv = document.getElementById('error-message');
            
            if (groupRequired.checked) {
                if (!maxGroupField.value || maxGroupField.value.trim() === '') {
                    showError("Please specify the maximum number of team members.");
                    return false;
                }
                
                if (parseInt(maxGroupField.value) < 2) {
                    showError("Team events must have at least 2 members.");
                    return false;
                }
            }
            return true;
        }

        function showError(message) {
            var errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // Scroll to error message
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Hide error after 5 seconds
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        function toggleGroupField() {
            var groupRequired = document.getElementById('is_group_required');
            var maxGroupField = document.getElementById('max_group_members');
            var maxGroupContainer = document.getElementById('maxGroupContainer');
            
            maxGroupField.disabled = !groupRequired.checked;
            maxGroupContainer.style.opacity = groupRequired.checked ? '1' : '0.5';
            
            if (groupRequired.checked) {
                maxGroupField.setAttribute('required', 'required');
                maxGroupContainer.classList.add('required-field');
            } else {
                maxGroupField.removeAttribute('required');
                maxGroupContainer.classList.remove('required-field');
                maxGroupField.value = '';
            }
        }

        function showConfirmation() {
            if (validateForm()) {
                var modal = document.getElementById('confirmModal');
                modal.style.display = 'block';
                // Add show class after a small delay to trigger animation
                setTimeout(() => {
                    modal.classList.add('show');
                }, 10);
            }
        }

        function hideConfirmation() {
            var modal = document.getElementById('confirmModal');
            modal.classList.remove('show');
            // Hide modal after animation completes
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function submitFormAndRedirect() {
            if (validateForm()) {
                var form = document.getElementById('editEventForm');
                form.addEventListener('submit', function() {
                    setTimeout(function() {
                        window.location.href = 'coach1.php';
                    }, 100);
                });
                form.submit();
            }
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            var modal = document.getElementById('confirmModal');
            if (event.target == modal) {
                hideConfirmation();
            }
        }

        // Initialize message auto-hide
        document.addEventListener('DOMContentLoaded', function() {
            var messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.display = 'none';
                }, 5000);
            });
            toggleGroupField();
        });
    </script>
</body>
</html>