function turl(templ){
	return site_url + "/wp-json/ngc-api/v1/Templates/"+templ;
}
angular.module("ngcApp",["ngRoute","ngAnimate"])
.config(function($routeProvider){
	$routeProvider.when("/Editor",{
		templateUrl:turl("Editor")
	}).when("/Clientes",{
		templateUrl:turl("Clientes"),
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
		columns:[
			{
				name:"name",
				header:"Nombre",
			},
			{
				name:"surname1",
				header: "Primer Apellido"
			},
			{
				name:"surname2",
				header:"Segundo Apellido"
			},
			{
				name:"email",
				hedaer:"Correo"
			},
			{
				name:"date",
				header:"Fecha"
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
			debugger;
			s.ngcGrid = {

			};
		}
	}
}]);