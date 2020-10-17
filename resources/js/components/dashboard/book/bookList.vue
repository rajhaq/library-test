<template>
	<v-content >
		<v-container fluid> 
			<v-overlay :value="showFullLoading" :absolute="absolute">
				<v-progress-circular indeterminate size="64"></v-progress-circular>
			</v-overlay>
				<ToolbarLeft>
					<v-spacer></v-spacer>
					<v-text-field
						v-model="filters.search"
						append-icon="search"
						label="Search"
						hide-details
						filled
						dense
						@input="getProduct"
					></v-text-field>
				</ToolbarLeft>
			<Breadcrumbs/>
			<v-row justify="center">
				<v-col sm="12" md="12" lg="12">
					<v-data-table color="white" :headers="headers" :items="dataList" hide-default-footer :items-per-page="15" class="elevation-4">
						<template v-slot:item.action="{ item }">
							<v-icon v-show="$store.state.authUser.type==1 || $store.state.authUser.type==2"  small @click="editItem(item)">edit</v-icon>
							<v-btn v-show="$store.state.authUser.type==1 || $store.state.authUser.type==2"  x-small color="warning"  @click="assignItem(item)">assign</v-btn>
							<v-btn v-show="$store.state.authUser.type==3 && !item.assign"  x-small color="warning"  @click="checkOut(item, $store.state.authUser.id)">checkout</v-btn>
							<v-chip  v-show="$store.state.authUser.type==3 && item.assign"  :color="'blue'" text-color="white" >Assigned</v-chip>

						</template>
						<template v-slot:item.status="{ item }">
							<v-chip :color="item.status?'green':'red'" text-color="white" >{{item.status?'Active':'Disable'}}</v-chip>
						</template>
						<template v-slot:no-data>
							<NoDataList :loading="loading" @initialize="initialize"></NoDataList>
						</template>
					</v-data-table>
					 <div class="text-center">
                                <v-pagination
                                v-model="filters.page"
                                :length="pageCount"
                                @input="getProduct"
                                ></v-pagination>
					</div>
				</v-col>
			</v-row>
			<v-btn bottom color="accent" dark fab fixed right @click="dialog = !dialog">
				<v-icon>mdi-plus</v-icon>
			</v-btn>
		</v-container>
        <v-dialog v-model="dialog" max-width="700px" persistent>
            <v-card>
				<v-card color="primary" dark tile flat >
					<v-card-title
					class="headline"
					v-text="formTitle"
					></v-card-title>
         		 </v-card >
                <v-card-title>
                </v-card-title>

                <v-card-text>
                    <v-container >
						<v-form ref="form" v-model="valid">
                        <v-row>
									
                                <v-col cols="12">
									<v-text-field
										v-model="editedItem.name"
										label="Name*"
										:rules="[v => !!v || 'Name is required']"
                                        required
										filled
									></v-text-field>
								</v-col>
                                <v-col cols="12">
									<v-text-field
										v-model="editedItem.author"
										label="Author*"
										:rules="[v => !!v || 'Author is required']"
                                        required
										filled
									></v-text-field>
								</v-col>
                                <v-col cols="12">
									<v-text-field
										v-model="editedItem.category"
										label="Category*"
										:rules="[v => !!v || 'Category is required']"
                                        required
										filled
									></v-text-field>
								</v-col>
                                <v-col cols="12">
									<v-text-field
										v-model="editedItem.stock"
										label="Stock*"
										:rules="[v => !!v || 'Stock is required']"
                                        required
										filled
									></v-text-field>
								</v-col>
                                <v-col
								cols="12"
								>
								<v-menu
									ref="menu"
									v-model="menu"
									:close-on-content-click="false"
									:return-value.sync="editedItem.publish_date"
									transition="scale-transition"
									offset-y
									min-width="290px"
								>
									<template v-slot:activator="{ on, attrs }">
									<v-text-field
										v-model="editedItem.publish_date"
										label="Publish Date"
										prepend-icon="mdi-calendar"
										readonly
										v-bind="attrs"
										v-on="on"
										filled
									></v-text-field>
									</template>
									<v-date-picker
									v-model="editedItem.publish_date"
									no-title
									scrollable
									>
									<v-spacer></v-spacer>
									<v-btn
										text
										color="primary"
										@click="menu = false"
									>
										Cancel
									</v-btn>
									<v-btn
										text
										color="primary"
										@click="$refs.menu.save(date)"
									>
										OK
									</v-btn>
									</v-date-picker>
								</v-menu>
								</v-col>
								<v-col cols="12">
									<v-select
										v-model="editedItem.status"
										:items="dataStatus"
										item-text="name"
										item-value="value"
										:rules="[v => !!v || 'Status is required']"
										label="Status"
										required
										filled
									></v-select>
								</v-col>
	                        </v-row>
						</v-form>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" text @click="close">Cancel</v-btn>
                    <v-btn
                        color="primary"
                        :loading="loading"
                        :disabled="loading"
                        text
                        @click="save"
                    >Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
		<v-dialog
			v-model="isAssign"
			max-width="350"
			>
			<v-card>
				<v-card-title class="headline">Assign a book to Customer</v-card-title>

				<v-card-text>
      <v-autocomplete
        v-model="assign_user"
        :items="dataCustomer"
        :loading="loading"
        :search-input.sync="searchCustomer"
        item-text="id"
        item-value="id"
        label="Customer ID"
        return-object
      >
	        <template v-slot:selection="{ attr, on, item, selected }">
        <v-chip
          color="blue-grey"
          class="white--text"
        >
          <span>{{item.first_name}}-{{item.id}}</span>
        </v-chip>
      </template>
	  <template v-slot:item="{ item }">
        <v-list-item-content>
          <v-list-item-title >{{item.first_name}}-{{item.id}}</v-list-item-title>
          <v-list-item-subtitle v-text="item.phone"></v-list-item-subtitle>
        </v-list-item-content>
      </template>
	  </v-autocomplete>
				</v-card-text>

				<v-card-actions>
				<v-spacer></v-spacer>

				<v-btn
					color="red"
					text
					@click="isAssign = false"
				>
					Close
				</v-btn>

				<v-btn
					color="green"
					text
					@click="assignAgent"
					:disabled="loading"
					:loading="loading"
				>
					Assign
				</v-btn>
				</v-card-actions>
			</v-card>
			</v-dialog>
		<v-snackbar
			v-model="snackbar"
			:vertical="snackvertical"
			:timeout="snacktimeout"
			:top="snacktop"
			:right="snackright"
			:color="snackcolor"
		>
			{{ snacktext }}
			<v-btn color="white" text @click="snackbar = false">Close</v-btn>
		</v-snackbar>
	</v-content>
</template>

<script>
import Breadcrumbs from "./../../common/Breadcrumbs"
import ToolbarLeft from "./../../common/ToolbarLeft"
import NoDataList from "./../../common/NoDataList"
import ImageModule from "./../../common/ImageModule"
import DeleteModal from "./../../common/DeleteModal";
export default {
	components:{
		Breadcrumbs,
		ToolbarLeft,
		NoDataList,
		ImageModule,
		DeleteModal
	},
	data: () => ({
		itemsPerPage:1,
        pageCount:1,
		assign_user:'',
		isAssign:false,
        menu:false,
        valid:false,
		search: "",
		absolute: true,
		loading: false,
		edit: true,
		dialog: false,
        dataList: [],
        dataCustomer: [],
        dataTopping: [],
		filters:
        {
			page:1,
			search:'',

        },
		headers: [
			{ text: "ID", value: "id" },
			{ text: "Name", value: "name" },
			{ text: "Category", value: "category" },
			{ text: "Author", value: "author" },
			{ text: "Publish date", value: "publish_date" },
			{ text: "Status", value: "status" },
			{ text: "Action", value: "action" }
		],
        editedIndex: -1,
		editedItem: {
			name: "",
			category: "",
			author: "",
			publish_date: new Date().toISOString().substr(0, 10),
            stock: 1,
            status: 1

		},
		defaultItem: {
			name: "",
			category: "",
			author: "",
			publish_date: new Date().toISOString().substr(0, 10),
			stock: 1,
			status: 1
		},
		dataStatus: [
			{ name: "Active", value: 1 },
			{ name: "Disable", value: 0 }
        ],
		dataGroup:[],
		dataIndex: null,
		searchCustomer:'',
	}),

	props: {
		source: String
	},
	computed: {
		formTitle() {
			return this.editedIndex === -1 ? "New Item" : "Edit Item";
		}
	},
	watch: {
		async searchCustomer(val)
		{

        // Items have already been requested
        if (this.loading) return

		this.loading = true
		try {
				let { data } = await axios({
					method: "get",
					url: "/app/user",
					params:
					{
						type:3,
						search: val

					}
				});
				this.dataCustomer = data;
				this.loading=false;
			} catch (e) {
				this.loading=false;
				
            }
		},
	},
	created() {
		this.initialize();
	},
	methods: {
		assignItem(item) {
			this.dataIndex = this.dataList.indexOf(item);
			this.editedItem = Object.assign({}, item);		
			this.isAssign=true;

		},
		async checkOut(item, user_id)
		{
			this.loading=true;
			try {
				let { data } = await axios({
					method: "post",
					url: "/app/assign",
					data:
					{
						user_id: user_id,
						book_id: item.id
					}
				});
				if (data.status) {
					this.snacks('Successfully Done','green')
					const index = this.dataList.indexOf(item);
					this.dataList[index].assign = data.data
					this.isAssign = false;
				}
				else
				{
					this.snacks(data.data,'red')
					this.loading = false;
				}

			} catch (e) {
				this.snacks('Operation Failed','red')
				this.loading = false;
			}
		},
		
		async assignAgent()
		{
			if(!this.assign_user)
			{
				this.snacks('Please add customer','red')
				return
			}
			this.loading=true;
			try {
				let { data } = await axios({
					method: "post",
					url: "/app/assign",
					data:
					{
						user_id: this.assign_user.id,
						book_id: this.editedItem.id
					}
				});
				if (data.status) {
					this.snacks('Successfully Done','green')
					this.isAssign = false;
				}
				else
				{
					this.snacks(data.data,'red')
					this.loading = false;
				}

			} catch (e) {
				this.snacks('Operation Failed','red')
				this.loading = false;
			}

		},
		close() {
			this.dialog = false;
			this.isAssign = false;
			this.loading = false;
		},
		async initialize() {
			this.loading=true;
			this.filters.page=1
			this.filters.search=''
			this.getProduct();

		},
		async getProduct()
        {
            this.loading=true;
            try {
				let { data } = await axios({
					method: "get",
                    url: "/app/book",
                    params: this.filters
				});
                this.dataList = data.data;
                this.itemsPerPage=data.per_page;
                this.pageCount=data.last_page;
				this.filters.page=data.current_page
				this.loading=false;
			} catch (e) {
				this.loading=false;

            }
        },
		editItem(item) {
			this.edit = false;
			this.editedIndex = this.dataList.indexOf(item);
			this.editedItem = Object.assign({}, item);
			this.dialog = true;
		},
		deleteItem(item) {
			const index = this.dataList.indexOf(item);
			confirm("Are you sure you want to delete this item?") &&
				this.dataList.splice(index, 1);
		},
		close() {
			this.dialog = false;
			this.loading = false;
			setTimeout(() => {
				this.editedItem = Object.assign({}, this.defaultItem);
				this.editedIndex = -1;
			}, 300);
		},
		reset () {
			this.$refs.form.reset();

		},
		async save() {
            if(!this.valid)
            {
                this.$refs.form.validate();
                return ;
            }
			this.loading = true;
			if (this.editedIndex > -1) 
			{
				try {
					let { data } = await axios({
						method: "put",
						url: "/app/book/" + this.editedItem.id,
						data: this.editedItem
					});
					if (data.status) {
						this.snacks("Successfully Updated", "green");
						Object.assign(this.dataList[this.editedIndex], this.editedItem);
						this.close();
					} else {
						this.snacks("Failed", "red");
						this.loading = false;
					}
				} catch (e) {
					this.snacks("Failed", "red");
					this.loading = false;
				}
			} else {
				try {
					let { data } = await axios({
						method: "post",
						url: "/app/book",
						data: this.editedItem
					});
					if (data.status) {
						this.dataList.unshift(data.data);
						this.snacks("Successfully Added", "green");
						this.close();
					} else {
						this.snacks("Failed! "+data.data, "red");
						this.loading = false;
					}
				} catch (e) {
					this.snacks("Failed! "+e, "red");
					this.loading = false;
				}
			}
		}
	}
};
</script>