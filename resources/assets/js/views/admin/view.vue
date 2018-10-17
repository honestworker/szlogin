<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">View Administrator Profile</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/admin">Administrator</router-link></li>
                    <li class="breadcrumb-item active">View Administrator Profile</li>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">View Administrator Profile</h4>
                        <admin-form :id="id"></admin-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import AdminForm from './form';
    import helper from '../../services/helper'

    export default {
        components : { AdminForm },
        data() {
            return {
                id: this.$route.params.id
            }
        },
    }
</script>