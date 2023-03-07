<template>
    <Layout :user="$page.props.user">
        
        <SelfAccreditor

        />

    </Layout>
</template>

<script setup>
import Layout from '../Components/Accreditor/AccreditorLayout.vue'
import SelfAccreditor from '../Components/Accreditor/SelfAccreditor/SelfAccreditor.vue'


</script>