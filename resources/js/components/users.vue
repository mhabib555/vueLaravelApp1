<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isAdmin()">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users</h3>

                <div class="card-tools">
                    <button class="btn btn-success" data-toggle="modal" @click="openNewUserModel">
                        Add New
                        <i class="fas fa-user-plus fa-fw"></i>
                    </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Type</th>
                      <th>Registered At</th>
                      <th>Modify</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in users.data">
                      <td>{{user.id}}</td>
                      <td>{{user.name | capitalaizeFirstText}}</td>
                      <td>{{user.email}}</td>
                      <td><span class="tag tag-success">{{user.type |capitalaizeFirstText}}</span></td>
                      <td>{{user.created_at }}</td>
                      <td>
                          <a href="#" @click.prevent="openEditUserModel(user)">
                              <i class="fas fa-edit text-orange"></i>
                          </a>
                          <a href="#" @click.prevent="deleteUser(user.id)">
                              <i class="fa fa-trash text-red"></i>
                          </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                  <pagination :data="users" @pagination-change-page="getResults"></pagination>              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
        <div class="row mt-5" v-if="!$gate.isAdmin()">
            <not-found></not-found>
        </div>
        <!-- / not-Found row -->
        <div class="modal" id="userDataModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editMode ? 'Update User Info' : 'Add New User' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? updateUser() : createUser()" @keydown="form.onKeydown($event)">
                    <div class="modal-body">
                        <!-- name input -->
                        <div class="form-group">
                            <input v-model="form.name" type="text" name="name" class="form-control" :class="{ 'is-invalid': form.errors.has('name') }" placeholder="Name">
                            <has-error :form="form" field="name"></has-error>
                        </div>
                        <!-- email input -->
                        <div class="form-group">
                            <input v-model="form.email" type="email" name="email" class="form-control" :class="{ 'is-invalid': form.errors.has('email') }" placeholder="Email">
                            <has-error :form="form" field="email"></has-error>
                        </div>
                        <!-- password input -->
                        <div class="form-group">
                            <input v-model="form.password" type="password" name="password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password') }" placeholder="Password">
                            <has-error :form="form" field="password"></has-error>
                        </div>
                        <!-- type input -->
                        <div class="form-group">
                            <select v-model="form.type" type="text" name="type" id="type" class="form-control" :class="{ 'is-invalid': form.errors.has('type') }" placeholder="Type">
                                <option value="">Select User Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">Standard User</option>
                                <option value="author">Author</option>
                            </select>                                    
                            <has-error :form="form" field="type"></has-error>
                        </div>
                        <!-- bio input -->
                        <div class="form-group">
                            <textarea v-model="form.bio" name="bio" id="bio" class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }" placeholder="Short Bio (Optional)"></textarea>
                            <has-error :form="form" field="bio"></has-error>
                        </div>
                        <!-- photo input -->
                        <div class="form-group">
                            <input v-model="form.photo" type="text" name="photo" class="form-control" :class="{ 'is-invalid': form.errors.has('photo') }" placeholder="Photo">
                            <has-error :form="form" field="photo"></has-error>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button :disabled="form.busy" type="submit" class="btn btn-primary">{{ editMode ? 'Update' : 'Create'}}</button>
                        <button type="button" v-on:click="resetNewUserForm" class="btn btn-warning">Reset</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data(){
            return{
                editMode: false,
                users: {}, 
                form: new Form({
                    id: '',
                    name: '',
                    email: '',
                    password: '', 
                    type: '',
                    bio: '',
                    photo: '',           
                })
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        methods: {
            openNewUserModel(){
                this.editMode = false;
                this.form.reset();
                $('#userDataModal').modal('show');
            },
            openEditUserModel(user){
                this.editMode = true;
                $('#userDataModal').modal('show');
                this.form.clear();
                this.form.fill(user);
            },
            createUser(){  
                this.$Progress.start();
                 // Submit the form via a POST request
                this.form.post('api/users')
                    .then(({ data }) => { 
                        if(this.form.successful){
                            $('#userDataModal').modal('hide');
                            Toast.fire({
                                icon: 'success',
                                title: 'User created successfully'
                            })
                            fireEvent.$emit('loadUsers');
                            this.$Progress.finish();
                        }
                    });
            },
            updateUser(id){  
                this.$Progress.start();
                 // Submit the form via a POST request
                this.form.put('api/users/'+this.form.id)
                    .then(({ data }) => { 
                        if(this.form.successful){
                            $('#userDataModal').modal('hide');
                            Toast.fire({
                                icon: 'success',
                                title: 'User updated successfully'
                            })
                            fireEvent.$emit('loadUsers');
                            this.$Progress.finish();
                        }
                    })
                    .catch(() => {
                        this.$Progress.fail();
                    });
            },
            loadUsers(){
                if(this.$gate.isAdmin()){
                    this.$Progress.start();
                    axios.get('api/users').then(({ data }) => {
                        this.users = data;
                        this.$Progress.finish();
                    });
                }
            },
            resetNewUserForm(){
                this.form.reset();
            },
            deleteUser(id){
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        this.$Progress.start();
                        axios.delete('api/users/'+id).then(({ data }) => {
                            console.log(data);
                            this.$Progress.finish();
                            fireEvent.$emit('loadUsers');
                            swal.fire(
                                'Deleted!',
                                'Your file has been deleted.',
                                'success'
                                )
                        }).catch(()=>{
                            swal.fire(
                                'Error!',
                                'Unable to delete the file.',
                                'error'
                                )

                        });
                    }
                })

            },
            getResults(page = 1) {
			axios.get('api/users?page=' + page)
				.then(response => {
					this.users = response.data;
				});
		    }
        },
        created(){
            this.loadUsers();
            fireEvent.$on('loadUsers', ()=>{
                this.loadUsers();
            });
        }
    }
</script>
