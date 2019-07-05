<template>
    <p class="p-2 mb-0 d-flex align-items-center message client">
        <span class="mr-2">{{ user.name }}:</span>

        <span class="lds-ellipsis">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </p>
</template>

<script>
    export default {
        props: ['user'],
    }
</script>
