<?php
session_start();
include 'config.php'; // Database connection using PDO
include 'common.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | FindHire</title>
    <!-- Include Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        /* Enhanced mobile responsiveness and visual appearance */
        @media (max-width: 768px) {
            #user-list {
                visibility: hidden;
                position: fixed;
                top: 50%;
                left: 50%;
                width: 90%;
                height: 80%;
                background-color: #ffffff;
                z-index: 100;
                overflow-y: auto;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
                transform: translate(-50%, -50%) scale(0);
                transition: transform 0.3s ease, visibility 0.3s;
                border-radius: 10px;
                padding: 20px;
            }
            #user-list.open {
                visibility: visible;
                transform: translate(-50%, -50%) scale(1);
            }
            #user-list-overlay {
                visibility: hidden;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 90;
                transition: visibility 0.3s;
            }
            #user-list-overlay.show {
                visibility: visible;
            }
            #chat-header-img {
                display: inline-block;
            }
            .chat-container {
                flex-direction: column;
                height: calc(100vh - 200px);
            }
            .message-form-container {
                padding: 1rem;
            }
            #toggle-user-list {
                display: block;
            }
            #close-user-list {
                display: block;
                background-color: #e5e7eb;
                padding: 10px;
                font-weight: bold;
                cursor: pointer;
                text-align: center;
                position: sticky;
                top: 0;
                z-index: 101;
            }
        }

        @media (min-width: 768px) {
            #user-list {
                flex: 1;
                display: block;
                transform: none;
            }
            #chat-messages {
                flex-grow: 1;
            }
            .message-form-container {
                border-top: 1px solid #e2e8f0;
                padding: 1rem;
            }
            #toggle-user-list {
                display: none;
            }
        }

        .chat-bubble {
            max-width: 80%;
            word-wrap: break-word;
            padding: 10px;
            border-radius: 15px;
            margin-bottom: 10px;
        }
        .chat-bubble-sender {
            background-color: #34d399;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .chat-bubble-receiver {
            background-color: #f3f4f6;
            color: #333;
            margin-right: auto;
            text-align: left;
        }
        .user-list-button {
            background-color: #f3f4f6;
            border: none;
            padding: 10px;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
        }
        .user-list-button:hover {
            background-color: #e2e8f0;
        }
        .user-list-button img {
            margin-right: 10px;
        }
        #chat-messages {
            overflow-y: auto;
            height: 500px;
            scroll-behavior: smooth;
        }
        .attachment-button {
            position: relative;
            cursor: pointer;
        }
        .attachment-button input[type="file"] {
            position: absolute;
            opacity: 0;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        #upload-progress {
            margin-top: 5px;
            font-size: 14px;
            color: #4b5563;
        }
        #record-progress {
            margin-top: 5px;
            font-size: 14px;
            color: #ef4444;
        }
        .voice-message-container {
            background-color: #34d399;
            color: white;
            padding: 10px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .voice-message-timestamp {
            font-size: 12px;
            color: #d1fae5;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-300 to-teal-600">
    <?php headerContent(); ?>

    <div id="user-list-overlay"></div>

    <div class="flex justify-center items-start min-h-screen pt-32 pb-20">
        <div class="w-full max-w-5x1 px-3 md:px-6 py-4 md:py-6 bg-white shadow-lg rounded-lg hover:shadow-2xl transition duration-300">
            <button id="toggle-user-list" class="bg-teal-500 text-white p-5 rounded-full fixed bottom-16 right-4 shadow-lg hover:bg-teal-600 transition lg:hidden">👥</button>
            <div class="flex flex-col lg:flex-row chat-container border rounded-lg shadow-xl h-full">
                <!-- User List -->
                <div id="user-list" class="w-full lg:w-1/3 border-r p-4 bg-gray-50 rounded-l-lg overflow-y-auto">
                    <div id="close-user-list" class="lg:hidden">Close User List</div>
                    <div class="mb-4">
                        <input type="text" id="user-search" placeholder="Search users... 🔍" class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                    <ul class="space-y-4">
                        <!-- User List Populated Here -->
                    </ul>
                </div>

                <!-- Chat Window -->
                <div class="w-full lg:w-2/3 flex flex-col bg-gray-100 rounded-r-lg">
                    <!-- Chat Header -->
                    <div id="chat-header" class="border-b p-4 font-bold bg-teal-500 text-white rounded-tr-lg flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            
                            <span id="chat-header-text">Select a user to start chatting</span><!-- Your existing HTML above -->
<script>
  // Listen for changes on the file input
  document.getElementById("attachment-input").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const newMessage = document.createElement("div");
        newMessage.classList.add("chat-message", "self-end", "p-4", "bg-gray-100", "rounded-lg", "shadow-sm");
        
        if (file.type.startsWith("image/")) {
          // If the file is an image, display it
          const imageUrl = e.target.result;
          const imgElement = document.createElement("img");
          imgElement.src = imageUrl;
          imgElement.alt = "Attachment";
          imgElement.classList.add("max-w-xs", "rounded-lg", "shadow-lg");
          newMessage.appendChild(imgElement);
        } else {
          // If the file is not an image, display the file name
          newMessage.textContent = `File: ${file.name}`;
        }
        
        // Append the new message to the chat messages container
        document.getElementById("chat-messages").appendChild(newMessage);
        // Scroll to the bottom of the chat
        document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;
      };
      reader.readAsDataURL(file);
    }
  });
</script>
<!-- Your existing HTML below -->

                        </div>
                    </div>
                    <!-- Chat Messages -->
                    <div id="chat-messages" class="flex-grow p-4 space-y-4 bg-white rounded-br-lg">
                        <!-- Messages Populated Here -->
                    </div>
                    <!-- Message Form -->
                    <div class="message-form-container">
                        <form id="message-form" class="flex items-center space-x-4">
                            <label class="attachment-button">
                                <i class="fas fa-paperclip fa-lg text-gray-500"></i>
                                <input type="file" id="attachment-input" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            </label>
                            <input type="text" id="message-input" placeholder="Type a message..." class="flex-grow px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <button type="button" id="record-voice-button" class="bg-red-400 text-white p-2 rounded-full hover:bg-red-500 transition">
                                <i class="fas fa-microphone"></i> Record
                            </button>
                            
                            <button type="submit" class="bg-teal-500 text-white px-4 py-3 rounded-lg hover:bg-teal-600 transition transform hover:scale-105">📩 Send</button>
                        </form>
                        <span id="upload-progress"></span>
                        <span id="record-progress"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php footerContent(); ?>

    <!-- Include jQuery for simplicity -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let chatWith = null;
            let chatWithUsername = '';
            let chatWithProfileImg = '';
            let mediaRecorder;
            let audioChunks = [];

            // Toggle user list on mobile
            $('#toggle-user-list').on('click', function() {
                $('#user-list').addClass('open');
                $('#user-list-overlay').addClass('show');
            });

            $('#close-user-list').on('click', function() {
                $('#user-list').removeClass('open');
                $('#user-list-overlay').removeClass('show');
            });

            $('#user-list-overlay').on('click', function() {
                $('#user-list').removeClass('open');
                $('#user-list-overlay').removeClass('show');
            });

            // Load user list
            function loadUsers(query = '') {
                $.ajax({
                    url: 'search_users.php',
                    method: 'GET',
                    data: { q: query },
                    success: function(response) {
                        let data = JSON.parse(response);
                        if (data.status === 'success') {
                            let users = data.users;
                            $('#user-list ul').empty();
                            users.forEach(user => {
                                $('#user-list ul').append(
                                    `<li class="user-list-button" data-id="${user.id}" data-profile-image="${user.profile_image}">
                                        <img src="${user.profile_image}" alt="${user.username}" class="w-12 h-12 rounded-full">
                                        <div>
                                            <p class="font-semibold">${user.username}</p>
                                            <p class="text-sm text-gray-600">Available for chat</p>
                                        </div>
                                    </li>`
                                );
                            });
                        }
                    }
                });
            }

            loadUsers();

            // Search users
            $('#user-search').on('input', function() {
                let query = $(this).val();
                loadUsers(query);
            });

            // Click on user to chat
            $(document).on('click', '.user-list-button', function() {
                chatWith = $(this).data('id');
                chatWithUsername = $(this).find('p.font-semibold').text();
                chatWithProfileImg = $(this).data('profile-image');
                $('#chat-header-img').attr('src', chatWithProfileImg).removeClass('hidden');
                $('#chat-header-text').text('Chat with ' + chatWithUsername);
                $('#user-list').removeClass('open'); // Hide user list on mobile when a chat is selected
                $('#user-list-overlay').removeClass('show');
                loadMessages();
            });

            // Load messages
            function loadMessages() {
                if (!chatWith) return;
                $.ajax({
                    url: 'get_messages.php',
                    method: 'GET',
                    data: { chat_with: chatWith },
                    success: function(response) {
                        let data = JSON.parse(response);
                        if (data.status === 'success') {
                            let messages = data.messages;
                            $('#chat-messages').empty();
                            messages.forEach(msg => {
                                let messageClass = msg.sender_id == <?php echo $user_id; ?> ? 'text-right' : 'text-left';
                                let messageBgClass = msg.sender_id == <?php echo $user_id; ?> ? 'chat-bubble chat-bubble-sender' : 'chat-bubble chat-bubble-receiver';
                                let messageSender = msg.sender_id == <?php echo $user_id; ?> ? 'You' : chatWithUsername;
                                let attachmentHtml = '';
                                if (msg.attachment) {
                                    let fileName = msg.attachment.split('/').pop(); // Extract the file name from the URL
                                    attachmentHtml = `<br><a href="${msg.attachment}" target="_blank" class="text-blue-500 underline">${fileName}</a>`;
                                }

                                let voiceMessageHtml = msg.voice_message ? `<br><div class="voice-message-container"><audio controls src="${msg.voice_message}"></audio><span class="voice-message-timestamp">${msg.created_at}</span></div>` : '';
                                $('#chat-messages').append(
                                    `<div class="mb-2 ${messageClass}">
                                        <div class="inline-block ${messageBgClass} p-3">
                                            <p class="text-sm">${escapeHtml(msg.message)}${attachmentHtml}${voiceMessageHtml}</p>
                                            <span class="text-xs text-gray-600 block mt-1">${messageSender} • ${msg.created_at}</span>
                                        </div>
                                    </div>`
                                );
                            });
                            $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                        }
                    }
                });
            }

            // Send message with upload progress
            $('#message-form').on('submit', function(e) {
                e.preventDefault();
                if (!chatWith) return;

                let message = $('#message-input').val();
                let attachment = $('#attachment-input')[0].files[0];
                let formData = new FormData();
                formData.append('receiver_id', chatWith);
                formData.append('message', message);
                if (attachment) {
                    formData.append('attachment', attachment);
                }

                $.ajax({
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                let percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);
                                $('#upload-progress').text(`Upload Progress: ${percentComplete}%`);
                            }
                        }, false);
                        return xhr;
                    },
                    url: 'send_message.php',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        let data = JSON.parse(response);
                        if (data.status === 'success') {
                            $('#message-input').val('');
                            $('#attachment-input').val('');
                            $('#upload-progress').text(''); // Reset progress text
                            loadMessages();
                        } else {
                            alert(data.message);
                        }
                    }
                });
            });

            // Polling to update messages every few seconds
            setInterval(loadMessages, 5000);

            // Voice recording
            $('#record-voice-button').on('click', function() {
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                    $('#recording-status').addClass('hidden');
                    $('#record-progress').text(''); // Reset recording progress text
                } else {
                    navigator.mediaDevices.getUserMedia({ audio: true })
                        .then(stream => {
                            mediaRecorder = new MediaRecorder(stream);
                            mediaRecorder.start();
                            $('#recording-status').removeClass('hidden');

                            let startTime = Date.now();
                            let recordInterval = setInterval(function() {
                                if (mediaRecorder && mediaRecorder.state === 'recording') {
                                    let elapsedTime = Math.floor((Date.now() - startTime) / 1000);
                                    $('#record-progress').text(`Recording... ${elapsedTime} seconds`);
                                } else {
                                    clearInterval(recordInterval);
                                }
                            }, 1000);

                            mediaRecorder.addEventListener("dataavailable", event => {
                                audioChunks.push(event.data);
                            });

                            mediaRecorder.addEventListener("stop", () => {
                                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                                let formData = new FormData();
                                formData.append('receiver_id', chatWith);
                                formData.append('voice_message', audioBlob);

                                $.ajax({
                                    url: 'send_message.php',
                                    method: 'POST',
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    success: function(response) {
                                        let data = JSON.parse(response);
                                        if (data.status === 'success') {
                                            loadMessages();
                                        } else {
                                            alert(data.message);
                                        }
                                    }
                                });
                                audioChunks = []; // Reset for next recording
                            });
                        })
                        .catch(error => {
                            alert('Unable to access microphone. Please check permissions.');
                            console.error('Microphone access error:', error);
                        });
                }
            });

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
</body>
</html>
