<template>
	<v-content >
		<v-container fluid> 
			<v-overlay :value="showFullLoading" :absolute="absolute">
				<v-progress-circular indeterminate size="64"></v-progress-circular>
			</v-overlay>
				<ToolbarLeft>
					<v-spacer></v-spacer>
					<v-text-field
						v-model="search"
						append-icon="search"
						label="Search"
						hide-details
						filled
						dense
					></v-text-field>
				</ToolbarLeft>
			<Breadcrumbs/>
			<v-row justify="center">
				<v-col sm="12" md="12" lg="12">
					<v-data-table color="white" :headers="headers" :items="dataList" :search="search" class="elevation-4">
						<template v-slot:item.action="{ item }">
							<v-btn x-small color="warning"  @click="assignItem(item)">return</v-btn>
						</template>
						<template v-slot:item.id="{ item }">
							{{item.book.id}}
						</template>
						<template v-slot:item.name="{ item }">
							{{item.book.name}}
						</template>
						<template v-slot:item.category="{ item }">
							{{item.book.category}}
						</template>
						<template v-slot:item.author="{ item }">
							{{item.book.author}}
						</template>
						<template v-slot:item.created_at="{ item }">
							{{ item.created_at | moment("dddd, MMMM Do YYYY") }}
						</template>
						<template v-slot:item.id="{ item }">
							{{item.book.id}}
						</template>
						<template v-slot:no-data>
							<NoDataList :loading="loading" @initialize="initialize"></NoDataList>
						</template>
					</v-data-table>
				</v-col>
			</v-row>
			<v-btn bottom color="accent" dark fab fixed right @click="dialog = !dialog">
				<v-icon>mdi-plus</v-icon>
			</v-btn>
		</v-container>
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
        
		headers: [
			{ text: "ID", value: "id" },
			{ text: "Name", value: "name" },
			{ text: "Category", value: "category" },
			{ text: "Author", value: "author" },
			{ text: "Checkout date", value: "created_at" },
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
		
		async assignItem(item)
		{
			this.loading=true;
			try {
				let { data } = await axios({
					method: "delete",
					url: "/app/assign/"+item.id,
				});
				if (data.status) {
					this.snacks('Successfully Done','green')
					const index = this.dataList.indexOf(item);
					this.dataList.splice(index, 1);
					this.loading=false;

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
			try {
				let { data } = await axios({
					method: "get",
					url: "/app/book/assign"
				});
				this.dataList = data;
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