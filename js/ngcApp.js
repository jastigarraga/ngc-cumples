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
	$scope.table = {
		transport:{
			read:{
				url: apiurl("Clientes"),
				method:"GET"
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
				editTemplateUrl:tediturl("BaseCellEditTemplate.html")
			},
			{
				name:"date",
				header:"Fecha",
				colWidth:"140px",
				templateUrl:tediturl("BaseCellTemplate.html"),
				editTemplateUrl:tediturl("BaseCellEditTemplate.html")
			}
		]
	}
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
			s.setEditIndex = function(index){
				s.inserting = false;
				s.editIndex = index;
				s.view = angular.copy(s.table.rows[index]);
			}
			s.cancel = function(){
				s.view = {};
				s.inserting = 0;
				s.editIndex = -1;
			}
			s.read = function(){
				var r = ((s.table.transport || {}).read || {});
				if(r.url){
					$http({
						url:r.url,
						method:(r.method || "GET"),
					}).then(function(resp){
						s.table.rows = resp.data;
					},function(error){

					});
				}
			};
			s.ngcGrid = {
				read: s.read
			};
			s.read();
		}
	}
}]);