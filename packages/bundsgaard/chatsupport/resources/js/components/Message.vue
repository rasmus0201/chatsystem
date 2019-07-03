<template>
    <p class="p-2 mb-0 message" :class="{ 'client': !message.from || (message.from.session_id !== user.session_id) }">
        {{ message.system ? 'System' : message.from.name }}: {{ message.message }}<br>
        <small>{{ format(message.created_at) }}</small>
    </p>
</template>

<script>
    import TimeService from '../services/timeService';

    export default {
        props: ['user', 'message'],

        methods: {
            format: TimeService.format
        }
    }
</script>
