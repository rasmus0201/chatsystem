<template>
    <div class="row" v-if="conversation.id && user.session_id">
        <div class="col-12">
            <div class="overflow-auto border border-primary rounded-sm position-relative messages-container" style="height: 400px;" ref="messagesContainer">

                <!-- Top bar -->
                <p class="p-2 mb-0 bg-light border-bottom position-sticky chat-window-top-status">
                    <span v-if="user.agent === true">
                        {{ conversation.user.name }} - ({{ conversation.user.language }}) <small>(Session: {{ conversation.user.session_id }})</small>
                    </span>
                    <button class="btn btn-sm btn-warning" v-on:click="leave">Afslut</button>
                </p>

                <div class="messages">
                    <message v-for="(_message, index) in conversation.messages" :user="user" :message="_message" :key="index + '-message'"></message>
                    <typing-message v-for="(client, index) in conversation.typingClients" :user="client" :key="index + '-typing'"></typing-message>
                </div>

                <!-- Status bar -->
                <message v-if="!conversation.clients.length" class="bg-light border-top position-absolute chat-window-bottom-status" :message="{ system: 1, message: 'Venter på betjening' }"></message>
            </div>
            <div class="row mt-4">
                <div class="col-9">
                    <input type="text" class="form-control" v-model="message" placeholder="Besked" v-on:keyup.enter="send" v-on:keyup="type($event)">
                </div>
                <div class="col-3">
                    <button type="button" class="btn btn-block btn-primary" v-on:click="send" :disabled="!conversation.clients.length">Send</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TimeService from '../services/timeService';

    export default {
        props: ['user', 'conversation'],

        data() {
            return {
                message: ''
            }
        },

        methods: {
            leave() {
                this.$emit('conversation:leave', {
                    type: 'conversation:leave',
                    data: {
                        conversation_id: this.conversation.id
                    }
                });
                this.scroll();
            },

            type: _.throttle(function(e) {
                if (e.metaKey || e.ctrlKey || e.altKey || e.key === 'Enter') {
                    return;
                }

                if (!this.conversation.clients.length) {
                    return;
                }

                this.$emit('message:type', {
                    type: 'message:type',
                    data: {
                        conversation_id: this.conversation.id
                    }
                });
                this.scroll();
            }, 350),

            send() {
                // TODO This should be allowed when we the agent can just get all messages.
                if (!this.conversation.clients.length) {
                    return;
                }

                const message = this.message.trim();

                if (message === '') {
                    return;
                }

                this.$emit('message:send', {
                    type: 'message:send',
                    data: {
                        message: this.message,
                        conversation_id: this.conversation.id
                    }
                });

                this.message = '';
            },

            scroll() {
                this.$nextTick(() => {
                    // At next tick the user could have disconnected
                    if (!this.$refs.messagesContainer) {
                        return;
                    }

                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                });
            }
        }
    }
</script>
