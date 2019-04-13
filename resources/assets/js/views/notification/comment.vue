<template>
    <div class="m-t-20">
        <div class="row">
            <div class="form-group col-md-6 col-lg-6 col-sm-12">
                <h5 class="card-title">User Information</h5>
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-12 text-right">
                        <label><strong>Full Name: </strong></label>
                    </div>
                    <div class="col-md-9 col-lg-9 col-sm-12">
                        <label v-text="full_name"></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-12 text-right">
                        <label><strong>Email: </strong></label>
                    </div>
                    <div class="col-md-9 col-lg-9 col-sm-12">
                        <label v-text="email"></label>
                    </div>
                </div>
                <h5 class="card-title">Notification Information</h5>
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-12 text-right">
                        <label><strong>Type: </strong></label>
                    </div>
                    <div class="col-md-9 col-lg-9 col-sm-12">
                        <label v-text="type"></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-12 text-right">
                        <label><strong>Group Name: </strong></label>
                    </div>
                    <div class="col-md-9 col-lg-9 col-sm-12">
                        <label v-text="group_name"></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-12 text-right">
                        <label><strong>Contents: </strong></label>
                    </div>
                    <div class="col-md-9 col-lg-9 col-sm-12">
                        <textarea v-model="contents" class="form-control" rows="5" readonly></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-12 text-right">
                        <label><strong>Created At: </strong></label>
                    </div>
                    <div class="col-md-9 col-lg-9 col-sm-12">
                        <label v-text="created_at"></label>
                    </div>
                    <br><br>
                    <div class="col-md-12">
                        <router-link to="/notification" class="btn btn-danger waves-effect waves-light m-t-10">Cancel</router-link>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6 col-lg-6 col-sm-12">
                <h4 class="card-title">Images</h4>
                <br><br>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4" v-for="image in images" v-bind:id="image.id">
                            <img v-bind:src="getNotificationImage(image)" class="img-responsive" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Comment</h4>
                        <div class="row m-t-20">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Full Name</label>
                                    <input class="form-control" v-model="filterCommentForm.full_name" @change="getComments">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input class="form-control" v-model="filterCommentForm.email" @change="getComments">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Contents</label>
                                    <input class="form-control" v-model="filterCommentForm.contents" @change="getComments">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterCommentForm.status" @change="getComments">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterCommentForm.sortBy" @change="getComments">
                                        <option value="email">Email</option>
                                        <option value="contents">Contents</option>
                                        <option value="created_at" selected>Created At</option>
                                        <option value="updated_at" selected>Updated At</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterCommentForm.order" @change="getComments">
                                        <option value="asc">Asc</option>
                                        <option value="desc" selected>Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Comment List</h4>
                        <h6 class="card-subtitle" v-if="comments.total">Total {{comments.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="comments.total">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Contents</th>
                                        <th>Images</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="comment in comments.data">
                                        <td class="user-profile" v-html="getCommentUser(comment)"></td>
                                        <td v-html="getCommentContents(comment)"></td>
                                        <td v-html="getCommentImages(comment)"></td>
                                        <td v-text="comment.created_at"></td>
                                        <td v-text="comment.updated_at"></td>
                                        <td v-html="getCommentStatus(comment)"></td>
                                        <td>
                                            <button v-if="comment.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleCommentStatus(comment)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleCommentStatus(comment)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteComment(comment)" data-toggle="tooltip" title="Delete Notification"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="comments" :limit=3 v-on:pagination-change-page="getComments"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterCommentForm.pageLength" @change="getComments" v-if="comments.total">
                                            <option value="5">5 per page</option>
                                            <option value="10">10 per page</option>
                                            <option value="25">25 per page</option>
                                            <option value="100">100 per page</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete Comment Modal -->
        <div class="modal" id="modal-delete-comment" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingComment">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete Comment
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this Comment?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteComment()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import pagination from 'laravel-vue-pagination'
    import ClickConfirm from 'click-confirm'
    import helper from '../../services/helper'
    import { stringToEmoticon } from 'emoticons-converter'
    
    export default {
        components : { pagination, ClickConfirm },
        data() {
            return {
                group_name: '',
                type: '',
                full_name: '',
                email: '',
                contents: '',
                created_at: '',
                updated_at: '',
                images: {},

                comments: {},

                comment_id: 0,

                filterCommentForm: {
                    id: this.id,
                    sortBy : 'updated_at',
                    order: 'desc',
                    full_name: '',
                    email : '',
                    contents: '',
                    created_at : '',
                    updated_at: '',
                    status: '',
                    pageLength: 100
                },

                deletingComment: 1,

                baseUrl : '',
            }
        },
        created() {
            this.getNotification();
            this.getComments();
        },
        props: ['id'],
        mounted() {
        },

        methods: {
            proceed() {
            },
            getNotification() {
                axios.post('/admin/notification/' + this.id)
                .then(response => {
                    this.group_name = response.data.notification.group.name;
                    this.type = response.data.notification.type.name;
                    this.full_name = response.data.notification.user.profile.full_name;
                    this.email = response.data.notification.user.email;
                    this.contents = stringToEmoticon(response.data.notification.contents);
                    this.created_at = response.data.notification.created_at;
                    this.updated_at = response.data.notification.updated_at;
                    this.images = response.data.notification.images;
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            getComments(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterCommentForm);
                axios.get('/admin/noti_comment?page=' + page + url).then(response => {
                    this.comments = response.data;                    
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            getNotificationImage(image) {
                if (image.url) {
                    return this.baseUrl + '/images/notifications/' + image.url;
                } else {
                    return this.baseUrl + '/images/common/no-image.png';
                }
            },

            getCommentUser(comment) {                
                var commentUserHtml = "";
                if (typeof comment.user != 'undefined' && comment.user !== null && comment.user !== '') {
                    commentUserHtml = "<div class='profile-img'>";
                    if (typeof comment.user.simple_profile != 'undefined' && comment.user.simple_profile !== null && comment.user.simple_profile !== '') {
                        if (comment.user.simple_profile.avatar) {
                            commentUserHtml = commentUserHtml + "<img src='/images/users/" + comment.user.simple_profile.avatar + "' alt='user'>";
                        } else {
                            commentUserHtml = commentUserHtml + "<img src='/images/common/no-user.png' alt='user'>";
                        }
                        commentUserHtml = commentUserHtml + "</div>";
                        commentUserHtml = commentUserHtml + "<p style='margin-bottom: 0px'>" + comment.user.simple_profile.full_name + "</p>";
                        commentUserHtml = commentUserHtml + "<p style='margin-bottom: 0px'>" + comment.user.email + "</p>";
                    } else {
                        commentUserHtml = commentUserHtml + "<img src='/images/common/no-user.png' alt='user'></div>";
                    }
                }
                return commentUserHtml;
            },
            getCommentContents(comment) {
                if (typeof comment.contents != 'undefined' && comment.contents !== null && comment.contents !== '') {
                    return stringToEmoticon(comment.contents);
                }
                return "";
            },
            getCommentImages(comment) {
                var image_html = "";
                if (typeof comment.images != 'undefined') {
                    if (comment.images.length > 0) {
                        for (var index = 0; index < comment.images.length; index++) {
                            image_html += '<img src="' + this.baseUrl + '/images/notifications/' + comment.images[index]['url'] + '" class="img-responsive-height img-max-height-100">';
                        }
                    }
                }
                return image_html;
            },
            getCommentStatus(comment) {
                return (comment.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },
            
            modalDeleteComment(comment) {
                this.comment_id = comment.id;
                $('#modal-delete-comment').modal('show');
            },
            deleteComment() {
                axios.delete('/admin/noti_comment/' + this.comment_id).then(response => {
                    $('#modal-delete-comment').modal('hide');
                    toastr['success'](response.data.message);
                    this.getComments();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-delete-comment').modal('hide');
                });
            },
            toggleCommentStatus(comment) {
                axios.post('/admin/noti_comment/status', {id: comment.id}).then((response) => {
                    this.getComments();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },

            start() {
                console.log('Starting File Management Component');
            },
        },
        computed: {
            
        }
    }
</script>