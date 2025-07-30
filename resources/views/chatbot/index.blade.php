@extends('layouts.app')

@section('title', 'Assistant IA')

@section('content')
<div class="container-fluid p-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            <!-- Header -->
            <div class="modern-card mb-4 text-center">
                <div class="p-4">
                    <h2 class="h4 mb-3 text-light">
                        <i class="fas fa-robot me-2 text-primary"></i>
                        Assistant IA - MyFuture
                    </h2>
                    <p class="text-secondary mb-0">Votre assistant intelligent pour vous aider avec vos candidatures</p>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="modern-card">
                <div class="p-0" style="height: 70vh; display: flex; flex-direction: column;">
                    
                    <!-- Chat Messages Area -->
                    <div id="chat-messages" class="flex-fill p-4" style="overflow-y: auto; max-height: 60vh;">
                        <!-- Welcome Message -->
                        <div class="d-flex align-items-start mb-4">
                            <div class="bot-avatar me-3">
                                <i class="fas fa-robot text-primary"></i>
                            </div>
                            <div class="message-bubble bot-message">
                                <p class="mb-0">👋 Bonjour ! Je suis votre assistant IA. Comment puis-je vous aider aujourd'hui ?</p>
                                <small class="text-muted d-block mt-1">Assistant IA</small>
                            </div>
                        </div>
                    </div>

                    <!-- Typing Indicator (Hidden by default) -->
                    <div id="typing-indicator" class="px-4 pb-2" style="display: none;">
                        <div class="d-flex align-items-start">
                            <div class="bot-avatar me-3">
                                <i class="fas fa-robot text-primary"></i>
                            </div>
                            <div class="typing-bubble">
                                <div class="typing-dots">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <small class="text-muted">Assistant IA est en train d'écrire...</small>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input Area -->
                    <div class="p-4 border-top" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                        <form id="chat-form" class="d-flex gap-3">
                            <div class="flex-fill">
                                <input 
                                    type="text" 
                                    id="message-input" 
                                    class="form-control modern-input" 
                                    placeholder="Tapez votre message..."
                                    autocomplete="off"
                                    maxlength="500"
                                >
                            </div>
                            <button type="submit" class="btn btn-primary px-4" id="send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Suggestions: Posez des questions sur les candidatures, les formations, ou demandez des conseils
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Chat specific styles */
    .bot-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-gradient));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--success-gradient));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
    }

    .message-bubble {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 18px;
        word-wrap: break-word;
        position: relative;
    }

    .bot-message {
        background: rgba(var(--primary-rgb), 0.1);
        border: 1px solid rgba(var(--primary-rgb), 0.2);
        color: var(--text-light);
    }

    .user-message {
        background: linear-gradient(135deg, var(--primary-gradient));
        color: white;
        margin-left: auto;
    }

    .user-message-container {
        flex-direction: row-reverse;
    }

    .user-message-container .message-bubble {
        margin-right: 0;
        margin-left: auto;
    }

    .typing-bubble {
        background: rgba(var(--primary-rgb), 0.1);
        border: 1px solid rgba(var(--primary-rgb), 0.2);
        padding: 12px 16px;
        border-radius: 18px;
        max-width: 80%;
    }

    .typing-dots {
        display: flex;
        gap: 4px;
        margin-bottom: 4px;
    }

    .typing-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary-color);
        animation: typing 1.4s infinite ease-in-out;
    }

    .typing-dots span:nth-child(1) {
        animation-delay: -0.32s;
    }

    .typing-dots span:nth-child(2) {
        animation-delay: -0.16s;
    }

    @keyframes typing {
        0%, 80%, 100% {
            transform: scale(0.8);
            opacity: 0.5;
        }
        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Scrollbar styling */
    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    #chat-messages::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: rgba(var(--primary-rgb), 0.5);
        border-radius: 3px;
    }

    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: rgba(var(--primary-rgb), 0.7);
    }

    /* Animation for new messages */
    .message-animate {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .message-bubble {
            max-width: 95%;
        }
        
        #chat-form {
            flex-direction: column;
            gap: 10px;
        }
        
        #chat-form .btn {
            align-self: flex-end;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const chatMessages = document.getElementById('chat-messages');
    const sendBtn = document.getElementById('send-btn');
    const typingIndicator = document.getElementById('typing-indicator');

    // Auto-scroll to bottom
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Add message to chat
    function addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `d-flex align-items-start mb-4 message-animate ${isUser ? 'user-message-container' : ''}`;
        
        const timestamp = new Date().toLocaleTimeString('fr-FR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });

        if (isUser) {
            messageDiv.innerHTML = `
                <div class="user-avatar me-3">
                    <i class="fas fa-user"></i>
                </div>
                <div class="message-bubble user-message">
                    <p class="mb-0">${escapeHtml(message)}</p>
                    <small class="text-light d-block mt-1 opacity-75">Vous - ${timestamp}</small>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="bot-avatar me-3">
                    <i class="fas fa-robot text-primary"></i>
                </div>
                <div class="message-bubble bot-message">
                    <p class="mb-0">${escapeHtml(message)}</p>
                    <small class="text-muted d-block mt-1">Assistant IA - ${timestamp}</small>
                </div>
            `;
        }

        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }

    // Show/hide typing indicator
    function showTyping() {
        typingIndicator.style.display = 'block';
        scrollToBottom();
    }

    function hideTyping() {
        typingIndicator.style.display = 'none';
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Handle form submission
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;

        // Disable input and button
        messageInput.disabled = true;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Add user message
        addMessage(message, true);
        
        // Clear input
        messageInput.value = '';
        
        // Show typing indicator
        showTyping();

        try {
            // Send message to server
            const response = await fetch('{{ route("chatbot.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();

            if (response.ok && data.response) {
                // Hide typing and add bot response
                hideTyping();
                addMessage(data.response);
            } else {
                hideTyping();
                addMessage('Désolé, une erreur s\'est produite. Veuillez réessayer plus tard.');
            }
        } catch (error) {
            console.error('Error:', error);
            hideTyping();
            addMessage('Erreur de connexion. Vérifiez votre connexion internet et réessayez.');
        } finally {
            // Re-enable input and button
            messageInput.disabled = false;
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            messageInput.focus();
        }
    });

    // Focus input on load
    messageInput.focus();

    // Handle Enter key
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });
});
</script>
@endsection
