{{-- Floating AI Assistant Chatbot --}}
<div id="aiChatbotWrapper" style="position: fixed; bottom: 18px; right: 18px; z-index: 9999; font-family: 'Inter', system-ui, -apple-system, sans-serif;">

    {{-- Chatbot Trigger Button --}}
    <button id="aiChatbotTrigger" class="btn border-0 shadow-lg d-flex align-items-center gap-2 rounded-pill px-3 py-2" style="background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); color: #fff; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
        <div class="position-relative d-flex align-items-center justify-content-center">
            <i class="bi bi-robot fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle" style="width:8px; height:8px;"></span>
        </div>
        <span class="fw-bold d-none d-sm-inline" style="font-size: 12.5px; letter-spacing: 0.2px;">Ask S-AI</span>
    </button>

    {{-- Chatbot Window --}}
    <div id="aiChatbotWindow" class="shadow-lg border bg-white d-none flex-column overflow-hidden" style="width: 320px; max-width: calc(100vw - 24px); height: 410px; border-radius: 16px; position: absolute; bottom: 55px; right: 0; transition: all 0.3s ease;">
        
        {{-- Header --}}
        <div class="px-3 py-2 text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 30px; height: 30px; font-size: 1rem;">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 13px;">Supply AI Assistant</div>
                    <div class="d-flex align-items-center gap-1 opacity-75" style="font-size: 10px;">
                        <span class="bg-success rounded-circle d-inline-block" style="width: 5px; height: 5px;"></span> Online &bull; FAQ Helper
                    </div>
                </div>
            </div>
            <button type="button" id="aiChatbotClose" class="btn btn-sm btn-link text-white p-1" style="font-size: 1.1rem; opacity: 0.8; text-decoration: none;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Messages Container --}}
        <div id="aiChatbotMessages" class="flex-grow-1 p-2 overflow-auto d-flex flex-column gap-2" style="background-color: #f8fafc; font-size: 12px; scroll-behavior: smooth;">
            
            {{-- Welcome Bot Message --}}
            <div class="d-flex gap-2 align-items-start max-w-85">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px; font-size: 0.75rem; margin-top:2px;">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="bg-white px-3 py-2 rounded-3 shadow-sm border text-dark" style="border-top-left-radius: 2px !important;">
                    👋 Hello! How can I help you today? Select a topic or ask a question!
                </div>
            </div>

            {{-- Quick FAQ Topic Chips --}}
            <div id="aiQuickChips" class="d-flex flex-wrap gap-1 ps-4 ms-1">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0.5 text-start chip-btn" style="font-size: 10.5px; font-weight: 500;" onclick="sendQuickQuestion('How to request supplies?')">📦 Request supplies</button>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0.5 text-start chip-btn" style="font-size: 10.5px; font-weight: 500;" onclick="sendQuickQuestion('How to track status?')">🔍 Track status</button>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0.5 text-start chip-btn" style="font-size: 10.5px; font-weight: 500;" onclick="sendQuickQuestion('PO Workflow')">⏱️ PO Workflow</button>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0.5 text-start chip-btn" style="font-size: 10.5px; font-weight: 500;" onclick="sendQuickQuestion('How to claim supplies?')">✅ Claim supplies</button>
            </div>

        </div>

        {{-- Typing Indicator --}}
        <div id="aiTypingIndicator" class="px-3 py-1 text-muted d-none" style="font-size: 10px; background-color: #f8fafc;">
            <i class="bi bi-three-dots loading-dots"></i> AI thinking...
        </div>

        {{-- Input Footer --}}
        <div class="p-2 bg-white border-top">
            <form id="aiChatbotForm" class="d-flex gap-2 align-items-center m-0">
                <input type="text" id="aiChatbotInput" class="form-control form-control-sm rounded-pill px-3 py-1.5 border" placeholder="Ask a question..." autocomplete="off" style="font-size: 12px;">
                <button type="submit" class="btn btn-primary rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 32px; height: 32px;">
                    <i class="bi bi-send-fill" style="font-size: 11px;"></i>
                </button>
            </form>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('aiChatbotTrigger');
    const windowEl = document.getElementById('aiChatbotWindow');
    const closeBtn = document.getElementById('aiChatbotClose');
    const form = document.getElementById('aiChatbotForm');
    const input = document.getElementById('aiChatbotInput');
    const messages = document.getElementById('aiChatbotMessages');
    const typing = id => document.getElementById(id);

    if (!trigger || !windowEl) return;

    trigger.addEventListener('click', () => {
        windowEl.classList.toggle('d-none');
        windowEl.classList.toggle('d-flex');
        if (!windowEl.classList.contains('d-none')) {
            input.focus();
        }
    });

    closeBtn.addEventListener('click', () => {
        windowEl.classList.add('d-none');
        windowEl.classList.remove('d-flex');
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        appendUserMessage(text);
        input.value = '';
        fetchBotReply(text);
    });

    window.sendQuickQuestion = function(text) {
        appendUserMessage(text);
        fetchBotReply(text);
    };

    function appendUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-end ms-auto max-w-85';
        div.innerHTML = `
            <div class="bg-primary text-white p-3 rounded-4 shadow-sm" style="border-top-right-radius: 4px !important; font-size: 13px;">
                ${escapeHtml(text)}
            </div>
        `;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    function appendBotMessage(text) {
        const div = document.createElement('div');
        div.className = 'd-flex gap-2 align-items-start max-w-85';
        
        // Convert Markdown formatting (bold, linebreaks, code) to HTML
        let formatted = escapeHtml(text)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/`([^`]+)`/g, '<code class="bg-light text-primary px-1 rounded">$1</code>')
            .replace(/\n/g, '<br>');

        div.innerHTML = `
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem; margin-top:2px;">
                <i class="bi bi-robot"></i>
            </div>
            <div class="bg-white p-3 rounded-4 shadow-sm border text-dark" style="border-top-left-radius: 4px !important; font-size: 13px; line-height: 1.5;">
                ${formatted}
            </div>
        `;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    function fetchBotReply(userMsg) {
        const typingEl = document.getElementById('aiTypingIndicator');
        if (typingEl) typingEl.classList.remove('d-none');

        fetch('{{ route("chatbot.ask") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: userMsg })
        })
        .then(r => r.json())
        .then(data => {
            if (typingEl) typingEl.classList.add('d-none');
            appendBotMessage(data.reply || 'Sorry, I am having trouble responding right now.');
        })
        .catch(() => {
            if (typingEl) typingEl.classList.add('d-none');
            appendBotMessage('I can help you with requesting supplies, tracking orders, and PO workflows. Try asking again!');
        });
    }

    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }
});
</script>
