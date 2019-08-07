import TimeService from '../services/timeService';
import WebSocketService from '../services/websocketService';

// define a mixin object
const commonChat = {
    data() {
        return {
            connection: null,
            typingTimeouts: []
        }
    },
    methods: {
        setRoom(room) {
            this.user.room = room;

            this.send({
                type: 'room:join',
                data: {
                    room_id: this.user.room.id,
                }
            });
        },

        openSocket() {
            this.connection = WebSocketService.new();

            this.connection.onopen = this.onOpen;
            this.connection.onmessage = this.onMessage;

            this.connection.onclose = function(e) { console.log(e, 'CLOSE'); };
            this.connection.onerror = function(e) { console.log(e, 'ERROR'); };
        },

        send(data) {
            this.connection.send(JSON.stringify(data));
        },

        onOpen(e) {
            this.send({
                type: 'session:connect',
                data: {
                    language: navigator.language,
                    name: this.user.name,
                    session_id: this.user.session_id
                }
            });
        },

        sendMessage(event) {
            this.send(event);

            this.conversation.messages.push({
                created_at: TimeService.now(),
                from: this.user,
                message: event.data.message,
                system: 0,
            });

            this.scroll();
        },

        setTyper(session_id) {
            this.typingTimeouts[session_id] = setTimeout(function() {
                this.clearTyper(session_id);
            }.bind(this), 1000);

            var index = this.getClientIndex(session_id);
            this.clients[index].typing = true;
        },

        clearTyper(session_id) {
            clearTimeout(this.typingTimeouts[session_id]);

            var index = this.getClientIndex(session_id);
            this.clients[index].typing = false;
        },

        scroll() {
            if (!this.$refs['chat']) {
                return;
            }

            this.$refs['chat'].scroll();
        }
    }
}

export { commonChat as default}
