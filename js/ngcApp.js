function turl(templ){
	return site_url + "/wp-content/plugins/ngc-cumples/Templates/"+templ;
}
function apiurl(action){
	return site_url + "/wp-json/ngc-api/v1/" + action;
}
function tediturl(templ){
	return site_url + "/wp-content/plugins/ngc-cumples/Templates/Editor/"+templ;
}
angular.module("ngcApp",["ngRoute","ngAnimate"])
.config(function($routeProvider){
	$routeProvider.when("/Editor",{
		templateUrl:turl("Editor")
	}).when("/Clientes",{
		templateUrl:turl("Clientes.html"),
		controller:"ngcClientesController"
	});
}).controller("ngcMainController",function($scope){
	$scope.menu = [{
		text: "Emails",
		href:"!/Editor"
	},{
		text:"Reemplazos",
		href:"!/Reemplazos"
	},{
		text:"Clientes",
		href:"!/Clientes",
		controller:"ngcClientesController"
	}];
})
.controller("ngcClientesController",function($scope){
	function data(d){
		if(typeof d.date !== "undefined"){
			d.date = d.date.toLocaleString();
		}
		return d;
	}
	$scope.table = {
		transport:{
			read:{
				url: apiurl("Clientes"),
				method:"GET"
			},
			insert:{
				url:apiurl("ClientesInsert"),
				data:data
			},
			update:{
				url:apiurl("ClientesUpdate"),
				data:function(n,old){
					return {new:data(n),old:data(old)};
				}
			},
			delete:{
				url:apiurl("ClientesDelete"),
				data:data
			}
		},
		columns:[
			{
				name:"name",
				header:"Nombre",
				colWidth:"150px",
				templateUrl:tediturl("BaseCellTemplate.html"),
				editTemplateUrl:tediturl("BaseCellEditTemplate.html")
			},
			{
				name:"surname1",
				header: "Primer Apellido",
				colWidth:"200px",
				templateUrl:tediturl("BaseCellTemplate.html"),
				editTemplateUrl:tediturl("BaseCellEditTemplate.html")
			},
			{
				name:"surname2",
				header:"Segundo Apellido",
				colWidth:"200px",
				templateUrl:tediturl("BaseCellTemplate.html"),
				editTemplateUrl:tediturl("BaseCellEditTemplate.html")
			},
			{
				name:"email",
				hedaer:"Correo",
				colWidth:"200px",
				templateUrl:tediturl("BaseCellTemplate.html"),
				editTemplateUrl:tediturl("BaseEmailTemplate.html")
			},
			{
				name:"date",
				header:"Fecha",
				colWidth:"140px",
				templateUrl:tediturl("BaseDateTemplate.html"),
				editTemplateUrl:tediturl("BaseDateEditTemplate.html")
			}
		],
		pageSize:"5",
		page:1,
		pageSizes: [{
			value:"5",
		},{
			value:"10",
		},{
			value:"20",
		},{
			value:"",
			text:"Todos"
		}]
	};
})
.directive("ngcGrid",["$http",function($http){
	return{
		restrict: "A",
		require: "ngModel",
		scope:{
			table:"=ngModel",
			ngcGrid:"=ngcGrid"
		},
		templateUrl:turl("Grid.html"),
		link: function(s,e,a,c){
			s.editIndex = -1;
			s.spinnerText = "Cargando..."
			s.setEditIndex = function(index){
				s.inserting = false;
				s.editIndex = index;
				s.view = angular.copy(s.table.rows[index]);
			};
			s.showInsertRow = function(){
				s.editIndex = -1;
				s.view = {};
				s.inserting = true;
			};
			s.cancel = function(){
				s.view = {};
				s.inserting = 0;
				s.editIndex = -1;
			}; 
			s.delete = function(index){
				s.cancel();
				if(confirm("¿Seguro que desea eliminar el registro?")){
					s.loading = true;
					var d = ((s.table.transport || {}).delete || {});
					if(d.url){
						$http({
							url:d.url,
							method:d.method||"POST",
							data:jQuery.param(s.table.rows[index]),
							headers: {'Content-Type': 'application/x-www-form-urlencoded'}
						}).then(function(response){
							s.table.rows.splice(index,1);
							s.loading = false;
						},function(error){
							alert(JSON.stringify(error));
							s.loading = false;
						})
					}else{
						s.table.rows.splice(index,1);
						s.loading = false;
					}
				}
				
			};
			s.insert = function(){
				var r = ((s.table.transport || {}).insert || {});
				s.loading = true;
				if(r.url){
					$http({
						url:r.url,
						method:r.method||"POST",
						data:jQuery.param(typeof r.data ==="undefined"?s.view:r.data(s.view)),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function(response){
						if(typeof r.success === "undefined"){
							if(typeof response.data !== "undefined"){
								if(typeof response.data.length !== "undefined"){
									for(var i in response.data){
										s.table.rows.unshift(response.data[i])
									}
								}else{
										s.table.rows.unshift(response);
								}
							}else{
									s.table.unshift(response)
							}
						}else{
							r.success(response);		
						}
						s.view = {};
						s.inserting = false;
						s.loading = false;
				},function(error){
						alert(JSON.stringify(error));
					
				});
				}else{
					s.table.rows.unshift(angular.copy(s.view));
					s.view = {};
					s.inserting = false;
					s.loading = false;
				}
			};
			s.update = function(index){
				s.loading = true;
				var u  = ((s.table.transport || {}).update || {});
				if(u.url){
					$http({
						url:u.url,
						method:u.method ||"POST",
						data:jQuery.param(typeof u.data ==="undefined"?{new:s.view,old:s.table.rows[index]}:u.data(s.view,s.table.rows[index])),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function(response){
						var data = response.data || reponse;
						data = data[0] || data;
						s.table.rows[index] = data;
						s.editIndex = -1;
						s.loading = false;
					},function(error){
						alert(JSON.stringify(error));
						s.loading = false;
					})
				}else {
					s.table.rows[index] = angular.copy(s.view);
					s.editIndex = -1;
					s.loading = false;
				}
			}
			s.read = function(){
				s.inserting = false;
				s.editIndex = -1;
				s.view = {};
				var r = ((s.table.transport || {}).read || {});
				var data = {
					page:s.table.page || 1,
					pageSize:s.table.pageSize
				};
				if(r.url){
					s.loading = true;
					$http({
						url:r.url,
						method:(r.method || "GET"),
						params:(typeof r.data ==="undefined"?data:r.data(data)),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					}).then(function(resp){
						s.table.total = resp.data.total;
						s.table.from = ((s.table.page || 1) - 1) * s.table.pageSize + 1;
						s.table.rows = resp.data.data || resp.data || r;
						s.table.to = s.table.from + s.table.rows.length - 1;
						s.pageCount = Math.ceil(s.table.total/s.table.pageSize);
						s.pages = [];
						var from = (s.table.page || 1) - 3;
						from = from<1?1:from;
						for(from;from<=s.pageCount && ((s.table.page||1)+3)>=from;from++){
							s.pages.push(from);
						} 
						s.loading = false;
					},function(error){
						s.loading=false;
					});
				}

			};
			s.setPage = function(page){
				s.table.page = page;
				s.read();
			}
			s.ngcGrid = {
				read: s.read
			};
			s.read();
		}
	}
}]);