<template>
    <div>
        <rooms :rooms="rooms" @select="setRoom($event)" v-if="!room"></rooms>
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
                <div class="overflow-auto border border-primary rounded-sm" style="height: 400px;" ref="messagesContainer">
                    <p class="p-2 mb-0 bg-light border-bottom" v-if="activeConversation.id" style="position: sticky;">
                        {{ activeConversation.user.name }} - ({{ activeConversation.user.language }}) <small>(Session: {{ activeConversation.user.session_id }})</small>
                        <button class="btn btn-sm btn-warning" v-on:click="unassign(activeConversation)">Afslut</button>
                    </p>
                    <div class="messages">
                        <p class="p-2 mb-0 message" :class="{ 'client': !message.from || (message.from.session_id !== identifier) }" v-for="(message, index) in activeConversation.messages" :key="index">
                            {{ message.system ? 'System' : message.from.name }}: {{ message.message }}<br>
                            <small>{{ format(message.created_at) }}</small>
                        </p>

                        <p class="p-2 mb-0 d-flex align-items-center message client" v-for="(client, index) in typingClients" :key="index + '-typing'">
                            <span class="mr-2">{{ client.name }}:</span>

                            <span class="lds-ellipsis">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-9">
                        <input type="text" class="form-control" v-model="message" placeholder="Besked" v-on:keyup.enter="sendMessage" v-on:keyup="typing($event)">
                    </div>
                    <div class="col-3">
                        <button type="button" class="btn btn-block btn-primary" v-on:click="sendMessage" :disabled="!activeConversation.id">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import TimeService from '../services/timeService';

    export default {
        data() {
            return {
                connection: null,
                identifier: window.Chatsupport.session,
                name: 'Supporter',

                message: '',
                room: null,

                conversations: [],
                assignedConversations: [],
                activeConversation: {},
                activeClients: [],

                typingTimeouts: []
            }
        },

        props: ['rooms'],

        computed: {
            typingClients () {
                var typing = [];

                for (const client of this.activeClients) {
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

            setRoom(room) {
                this.room = room;

                this.send({
                    type: 'session:room',
                    data: {
                        room_id: this.room.id,
                    }
                });
            },

            openSocket() {
                var protocol = location.protocol === 'http:' ? 'ws' : 'wss';
                var host = protocol + '://' + window.location.hostname;
                var url = host + '/websocket';

                if (host.match(/\.(test|localhost|dev)/)) {
                    var url = host + ':9000';
                }

                this.connection = new WebSocket(url);

                this.connection.onopen = this.onOpen;
                this.connection.onmessage = this.onMessage;

                // Make some kind of error handler when connection gets closed
                // or an error occurred.
                this.connection.onclose = function(e) { console.log(e, 'CLOSE'); };
                this.connection.onerror = function(e) { console.log(e, 'ERROR'); };
            },

            onOpen(e) {
                this.send({
                    type: 'session:connect',
                    data: {
                        language: navigator.language,
                        name: this.name,
                        identifier: this.identifier,
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
                        this.room = { id: data.room_id };
                        break;
                    case 'typing':
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
                        if (this.activeConversation.id && !newConversations.includes(this.activeConversation.id)) {
                            this.activeConversation.messages.push({
                                message: this.activeConversation.user.name + ' har lukket chatten.',
                                sender: 'System',
                                time: TimeService.now()
                            });
                            this.activeConversation.closed = true;
                        }

                        this.conversations = data.conversations;
                        this.scroll();
                        break;
                }
            },

            send(data) {
                this.connection.send(JSON.stringify(data));
            },

            sendMessage() {
                const message = this.message.trim();
                const c_id = this.activeConversation.id;

                if (message === '' || !c_id) {
                    return;
                }

                this.send({
                    type: 'message',
                    data: {
                        message: message,
                        conversation: c_id
                    }
                });

                var messages = this.assignedConversations[c_id].messages;
                var msg = {
                    from: this.identifier,
                    message: message,
                    sender: this.name,
                    time: TimeService.now()
                };
                messages.push(msg);
                this.$set(this.assignedConversations[c_id], 'messages', messages);

                this.message = '';

                this.scroll();
            },

            typing: _.throttle(function(e) {
                if (e.metaKey || e.ctrlKey || e.altKey || e.key === 'Enter') {
                    return;
                }

                if (!this.activeClients.length) {
                    return;
                }

                for (const client of this.activeClients) {
                    this.send({
                        type: 'typing',
                        data: {
                            to: client.identifier
                        }
                    });
                }
            }, 350),

            assign(conversation) {
                if (!this.assignedTo(conversation.id)) {
                    this.send({
                        type: 'assign',
                        data: {
                            // assignee: this.identifier,
                            conversation_id: conversation.id
                        }
                    });

                    conversation.messages = [];
                    conversation.unseen = false;
                    conversation.closed = false;

                    this.assignedConversations[conversation.id] = conversation;
                }

                this.assignedConversations[conversation.id].unseen = false;
                this.activeConversation = this.assignedConversations[conversation.id];
            },

            unassign(conversation) {
                this.send({
                    type: 'unassign',
                    data: {
                        assignee: this.identifier,
                        to: client.identifier
                    }
                });

                this.activeConversation = {};
                delete this.assignedConversations[conversation.id];
            },

            assignedTo(id) {
                return typeof this.assignedConversations[id] !== 'undefined';
            },

            isActiveConversation(id) {
                return this.activeConversation.id === id;
            },

            unseenMessages(id) {
                if (typeof this.assignedConversations[id] === 'undefined') {
                    return false;
                }

                return this.assignedConversations[id].unseen === true;
            },

            getClientIndex(identifier) {
                for (var i = 0; i < this.activeClients.length; i++) {
                    if (identifier === this.activeClients[i].identifier) {
                        return i;
                    }
                }

                return undefined;
            },

            setTyper(identifier) {
                this.typingTimeouts[identifier] = setTimeout(function() {
                    this.clearTyper(identifier);
                }.bind(this), 1000);

                var index = this.getClientIndex(identifier);
                this.activeClients[index].typing = true;
            },

            clearTyper(identifier) {
                clearTimeout(this.typingTimeouts[identifier]);

                var index = this.getClientIndex(identifier);
                this.activeClients[index].typing = false;
            },

            scroll() {
                this.$nextTick(() => {
                    if (!this.$refs.messagesContainer) {
                        return;
                    }

                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                });
            }
        },
    }
</script>
