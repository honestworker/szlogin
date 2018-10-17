<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0" v-if="id != 0">Edit Advertisement</h3>
                <h3 class="text-themecolor m-b-0 m-t-0" v-else>Create Advertisement</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/advertisement">Advertisement</router-link></li>
                    <li class="breadcrumb-item active" v-if="id != 0">Edit Advertisement</li>
                    <li class="breadcrumb-item active" v-else>Create Advertisement</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" v-if="id != 0">Edit Advertisement</h4>
                        <h4 class="card-title" v-else>Create Advertisement</h4>
                        <advertisement-form :id="id"></advertisement-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import AdvertisementForm from './form';
    import helper from '../../services/helper'

    export default {
        components : { AdvertisementForm },
        data() {
            return {
                id: this.$route.params.id
            }
        },
    }
</script>