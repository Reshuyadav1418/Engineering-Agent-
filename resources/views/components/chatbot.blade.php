<div 
    x-data="chatbotComponent()"
    x-init="init()"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end"
    id="global-chatbot"
>
    <!-- Chat Window -->
    <div 
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        style="display:none;"
        class="w-96 h-[480px] bg-[#0e1018]/95 border border-slate-700/50 rounded-2xl shadow-2xl flex flex-col mb-4 overflow-hidden"
        id="chatbot-window"
    >
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-900 to-purple-900 px-5 py-4 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-md">
                    🤖
                </div>
                <div>
                    <h4 class="text-white text-sm font-bold tracking-wide">Engineering AI Assistant</h4>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] text-indigo-200 font-semibold uppercase tracking-wider">Online</span>
                    </div>
                </div>
            </div>
            <button 
                @click="toggle()"
                class="text-slate-400 hover:text-white transition-colors p-1 hover:bg-white/10 rounded-lg outline-none"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Chat Body / Messages -->
        <div 
            x-ref="chatBody"
            class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth"
            style="background: rgba(14, 16, 24, 0.5); scrollbar-width: thin; scrollbar-color: rgba(99, 102, 241, 0.2) transparent;"
        >
            <!-- Welcome Message (Only show if history is empty) -->
            <template x-if="messages.length === 0 && !isLoading">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center flex-shrink-0 text-sm">
                        🤖
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl rounded-tl-none px-4 py-3 text-slate-300 text-xs leading-relaxed max-w-[80%]">
                        Hello! I am your Engineering AI Assistant. You can ask me questions about performance leaderboards, employee scores, teams, or commits. How can I help you today?
                    </div>
                </div>
            </template>

            <!-- Message Loop -->
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end gap-3' : 'flex gap-3'">
                    <!-- AI Avatar (Left) -->
                    <template x-if="msg.role !== 'user'">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center flex-shrink-0 text-sm shadow-sm border border-slate-700/50">
                            🤖
                        </div>
                    </template>

                    <!-- Bubble -->
                    <div 
                        :class="msg.role === 'user' 
                            ? 'bg-gradient-to-tr from-indigo-600 to-indigo-700 text-white rounded-2xl rounded-tr-none px-4 py-3 text-xs leading-relaxed max-w-[80%] shadow-md border border-indigo-500/20' 
                            : 'bg-slate-900 border border-slate-800 text-slate-300 rounded-2xl rounded-tl-none px-4 py-3 text-xs leading-relaxed max-w-[80%] shadow-md'"
                        x-text="msg.message"
                        style="white-space: pre-wrap;"
                    ></div>

                    <!-- User Avatar (Right) -->
                    <template x-if="msg.role === 'user'">
                        <div class="w-8 h-8 rounded-lg bg-indigo-900 text-indigo-200 flex items-center justify-center flex-shrink-0 font-bold text-xs shadow-sm border border-indigo-800">
                            U
                        </div>
                    </template>
                </div>
            </template>

            <!-- Loading Indicator -->
            <template x-if="isLoading">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center flex-shrink-0 text-sm border border-slate-700/50">
                        🤖
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl rounded-tl-none px-4 py-3 flex items-center gap-1.5 max-w-[80%]">
                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0ms;"></span>
                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 150ms;"></span>
                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 300ms;"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer / Input Form -->
        <form 
            @submit.prevent="sendMessage()" 
            class="p-3 border-t border-slate-800 bg-slate-950/60 flex items-center gap-2"
        >
            <input 
                type="text" 
                x-model="message"
                placeholder="Ask AI anything..." 
                class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500/80 transition-colors"
                :disabled="isLoading"
            >
            <button 
                type="submit"
                class="p-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed outline-none flex items-center justify-center"
                :disabled="!message.trim() || isLoading"
            >
                <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                </svg>
            </button>
        </form>
    </div>

    <!-- Floating Toggle Button -->
    <button 
        @click="toggle()"
        class="w-14 h-14 bg-gradient-to-tr from-indigo-600 to-purple-600 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-105 transition-all duration-200 border border-indigo-400/30 focus:outline-none outline-none"
        id="chatbot-trigger-btn"
    >
        <span class="text-2xl" x-show="!isOpen">🤖</span>
        <span class="text-xl font-bold" x-show="isOpen" style="display:none;">✕</span>
    </button>
</div>

<script>
    function chatbotComponent() {
        return {
            isOpen: false,
            message: '',
            messages: [],
            isLoading: false,
            csrfToken: '{{ csrf_token() }}',

            init() {
                this.isOpen = localStorage.getItem('chatbot_open') === 'true';
                this.fetchHistory();
            },

            toggle() {
                this.isOpen = !this.isOpen;
                localStorage.setItem('chatbot_open', this.isOpen);
                if (this.isOpen) {
                    this.$nextTick(() => { this.scrollToBottom(); });
                }
            },

            async fetchHistory() {
                try {
                    const res = await fetch('{{ route('chat.history') }}');
                    const data = await res.json();
                    this.messages = data.history || [];
                    this.$nextTick(() => { this.scrollToBottom(); });
                } catch (e) {
                    console.error('Error fetching chat history', e);
                }
            },

            async sendMessage() {
                if (!this.message.trim()) return;

                const text = this.message;
                this.message = '';

                // Push user message locally
                this.messages.push({
                    role: 'user',
                    message: text
                });

                this.isLoading = true;
                this.$nextTick(() => { this.scrollToBottom(); });

                try {
                    const res = await fetch('{{ route('chat.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({ message: text })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.messages.push({
                            role: 'assistant',
                            message: data.response
                        });
                    } else {
                        this.messages.push({
                            role: 'assistant',
                            message: 'Error: ' + (data.error || 'Failed to get response.')
                        });
                    }
                } catch (e) {
                    this.messages.push({
                        role: 'assistant',
                        message: 'Network error occurred. Please try again.'
                    });
                } finally {
                    this.isLoading = false;
                    this.$nextTick(() => { this.scrollToBottom(); });
                }
            },

            scrollToBottom() {
                const el = this.$refs.chatBody;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            }
        }
    }
</script>
