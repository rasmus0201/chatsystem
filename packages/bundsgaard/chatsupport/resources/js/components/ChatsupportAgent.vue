<template>
    <div>
        <rooms :rooms="rooms" @select="setRoom($event)" v-if="!user.room.id"></rooms>
        <div class="row" v-else>
            <div class="col-4">
                <ul class="list-group" style="min-height: 400px;">
                    <li class="list-group-item d-flex justify-content-between align-items-center" :class="{ 'active': isActiveConversation(conversation.id) }" v-for="(conversation, index) in conversations" :key="index">
                        <div class="d-flex align-items-center" v-on:click="assign(conversation)">
                            <span v-if="unseenMessages(conversation.id)" class="indicator bg-primary mr-1"></span>
                            <span>
                                {{ conversation.user.name }}
                            </span>
                        </div>

                        <div>
                            <button type="button" class="btn btn-primary btn-sm" v-on:click="assign(conversation)" v-text="assignedTo(conversation.id) ? 'Vælg' : 'Forbind'"></button>
                            <button type="button" class="btn btn-danger btn-sm" v-on:click="ban(client)">Ban</button>
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center" v-if="conversations.length === 0">
                        Der er ingen brugere.
                    </li>
                </ul>
            </div>
            <div class="col-8">
                <chat-window
                    ref="chat"
                    :user="user"
                    :disabled="false"
                    :conversation="conversation"
                    @conversation:leave="unassign($event)"
                    @message:send="sendMessage($event)"
                    @message:typing="send($event)">

                </chat-window>
            </div>
        </div>
    </div>

</template>

<script>
    import TimeService from '../services/timeService';
    import commonChat from '../services/commonChat';

    export default {
        mixins: [commonChat],
        data() {
            return {
                user: {
                    agent: true,
                    room: {},
                    name: 'Supporter',
                    session_id: window.Chatsupport.session
                },

                conversations: [],
                assignedConversations: [],
                clients: [],
                conversation: {},
            }
        },

        props: ['rooms'],

        computed: {
            typingClients () {
                var typing = [];

                for (const client of this.clients) {
                    if (client.typing) {
                        typing.push(client);
                    }
                }

                return typing;
            }
        },

        created() {
            this.openSocket();
        },

        methods: {
            format: TimeService.format,

            onOpen(e) {
                this.send({
                    type: 'session:connect',
                    data: {
                        language: navigator.language,
                        name: this.user.name,
                        session_id: this.user.session_id,
                        credentials: {
                            username: 'test',
                            password: 'test'
                        }
                    }
                });
            },

            onMessage(e) {
                var {type, data} = JSON.parse(e.data);

                switch (type) {
                    case 'message':
                        const c_id = data.conversation_id;

                        if (!this.assignedTo(c_id)) {
                            return;
                        }

                        if (!this.isActiveConversation(c_id)) {
                            this.$set(this.assignedConversations[c_id], 'unseen', true);
                            this.$forceUpdate();
                        }

                        var messages = this.assignedConversations[c_id].messages;
                        messages.push(data.message);
                        this.$set(this.assignedConversations[c_id], 'messages', messages);

                        this.messages.push(data);
                        this.clearTyper(data.message.user.session_id);
                        this.scroll();

                        break;
                    case 'room':
                        this.user.room = { id: data.room_id };
                        break;
                    case 'message:typing':
                        if (!this.assignedTo(data.conversation_id)) {
                            return;
                        }

                        this.clearTyper(data.from);
                        this.setTyper(data.from);
                        this.scroll();

                        break;
                    case 'conversation:list':
                        const newConversations = data.conversations.map((conversation) => {
                            return conversation.id;
                        });

                        // Active client has disconnected
                        if (this.conversation.id && !newConversations.includes(this.conversation.id)) {
                            this.conversation.messages.push({
                                message: this.conversation.user.name + ' har lukket chatten.',
                                sender: 'System',
                                time: TimeService.now()
                            });
                            this.conversation.closed = true;
                        }

                        this.conversations = data.conversations;
                        this.scroll();
                        break;
                }
            },

            sendMessage(event) {
                const conversation_id = this.conversation.id;

                if (!conversation_id) {
                    return;
                }

                this.send(event);

                var messages = this.assignedConversations[c_id].messages;
                var msg = {
                    from: this.session_id,
                    message: event.data.message,
                    sender: this.name,
                    time: TimeService.now()
                };
                messages.push(msg);
                this.$set(this.assignedConversations[c_id], 'messages', messages);

                this.scroll();
            },

            assign(conversation) {
                if (!this.assignedTo(conversation.id)) {
                    this.send({
                        type: 'assign',
                        data: {
                            conversation_id: conversation.id
                        }
                    });

                    conversation.messages = [];
                    conversation.unseen = false;
                    conversation.closed = false;

                    this.assignedConversations[conversation.id] = conversation;
                }

                this.assignedConversations[conversation.id].unseen = false;
                this.conversation = this.assignedConversations[conversation.id];
            },

            unassign(event) {
                this.conversation = {};
                delete this.assignedConversations[event.data.conversation_id];
            },

            assignedTo(id) {
                return typeof this.assignedConversations[id] !== 'undefined';
            },

            isActiveConversation(id) {
                return this.conversation.id === id;
            },

            unseenMessages(id) {
                if (typeof this.assignedConversations[id] === 'undefined') {
                    return false;
                }

                return this.assignedConversations[id].unseen === true;
            },

            getClientIndex(session_id) {
                for (var i = 0; i < this.clients.length; i++) {
                    if (session_id === this.clients[i].session_id) {
                        return i;
                    }
                }

                return undefined;
            },
        },
    }
</script>
