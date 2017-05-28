function turl(templ){
	return site_url + "/wp-content/plugins/ngc-cumples/templates/"+templ;
}
function apiurl(action){
	return site_url + "/wp-json/ngc-api/v1/" + action;
}
function tediturl(templ){
	return site_url + "/wp-content/plugins/ngc-cumples/templates/Editor/"+templ;
}
angular.module("ngcApp",["ngRoute","ngAnimate"])
.config(function($routeProvider){
	$routeProvider.when("/Plantilla",{
		templateUrl:turl("Editor.html"),
		controller:"ngcPlantillaController"
	}).when("/Clientes",{
		templateUrl:turl("Clientes.html"),
		controller:"ngcClientesController"
	}).when("/Config",{
		templateUrl:turl("Configuration.html"),
		controller:"ngcConfigController"
	});
})
.controller("ngcMainController",["$scope","$window",function($scope,$window){
	$scope.menu = [{
		text: "Plantilla",
		href:"!/Plantilla"
	},{
		text:"Clientes",
		href:"!/Clientes"
	},{
		text:"Configuración",
		href:"!/Config"
	}];
	$scope.isActive = function(href){
		return $window.location.hash == "#" + href;
	};
}])
.controller("ngcClientesController",function($scope){
	$scope.filter = {};
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
				method:"GET",
				data:function(d){
					d.filter = $scope.filter;
					return d;
				}
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
			},{
				name:"last_sent",
				header:"Enviado en",
				colWidth:"140px",
				templateUrl:tediturl("BaseDateTemplate.html"),
				editTemplateUrl:tediturl("BaseDateTemplate.html")
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
	$scope.search = function(){
		$scope.gridClientes.read();
	};
})
.controller("ngcPlantillaController",["$scope","$http",function($scope,$http){
	$scope.plantilla ="<h1>Cargando...</h1>";
}])
.controller("ngcConfigController",["$scope","$http",function($scope,$http){
	function load(){
		$scope.loading = true;
		$http({
			url:apiurl("GetConfig")
		})
		.then(function(response){
			var mailConf = response.data.mail;
			if(typeof mailConf.mail_port !== "undefined"){
				mailConf.mail_port = Number(mailConf.mail_port);
			}
			$scope.mail = mailConf;
			$scope.cron = response.data.cron;
			$scope.loading = false;
		},function(error){})
	}
	load();
	$scope.checkMailConfig = function(){
		$scope.loading = true;
		$scope.emailConfigForm.$setSubmitted(true);
		if($scope.emailConfigForm.$valid){
			$http({
				url:apiurl("CheckMailConfig"),
				method:"POST",
				data:$scope.mail
			}).then(function(){
				$scope.loading = false;
			},function(){
				$scope.loading = false;
			})
		}
	};
	$scope.saveMail = function(){
		$scope.loading = true;
		$scope.emailConfigForm.$setSubmitted(true);
		if($scope.emailConfigForm.$valid){
			$http({
				url:apiurl("SaveMailConfig"),
				method:"POST",
				data:$scope.mail
			}).then(load);
		}
	};
	$scope.saveCron = function(){
		$http({
			url:apiurl("SaveCronConfig"),
			method:"POST",
			data:{h:angular.element("#ngc_cron_h").val()}
		}).then(load);
	};
}])
.directive("ngcEditor",["$http",function($http){
	return{
		restrict:"A",
		require:"ngModel",
		scope:{
			html:"=ngModel"
		},
		templateUrl:turl("WYSIWYGEditor.html"),
		link:function(s,e,a,c){
			s.iframe = e.find("iframe")[0];
			s.doc = s.iframe.contentDocument;
			s.doc.designMode = "on";
			s.fonts = ["Georgia","Book Antiqua","Times New Roman","Arial","Arial Black","Comic Sans MS","Impact","Tahoma",
			"Helvetica","Verdana","Courier New","Lucida Console" ];
			function cmd(evt,command){
				s.doc.execCommand(command);
				evt.preventDefault();
				evt.stopPropagation();
				s.iframe.focus();
			}
			s.textMode = function(value){
				s.htmlMode = !value;
				if(!value){
					s.html = s.doc.body.innerHTML;
				}
			};
			s.fontSizeChange = function(){
				s.doc.execCommand("fontSize", false, s.fontSize);
			};
			s.fontNameChange = function(){
				s.doc.execCommand("fontName", false, s.fontName);
			};
			s.foreColorChange = function(){
				s.doc.execCommand("foreColor",false, s.foreColor);
			};
			c.$render = function(){
				s.doc.body.innerHTML = c.$viewValue;

			};
			s.toolbars = [
				{
					buttons:[
						{
							icon:"ngc-icon ngc-icon-save",
							action:function($event){
								s.save();
							}
						},{
							icon:"ngc-icon ngc-icon-folder",
							action:function($event){
								s.load();
							}
						}
					]
				},{
					buttons:[
						{
							icon:"ngc-icon ngc-icon-undo",
							action:function($event){
								cmd($event,"undo");
							}
						},{
							icon:"ngc-icon ngc-icon-redo",
							action:function($event){
								cmd($event,"redo");
							}
						}
					]
				},{
					buttons:[
						{
							icon:"ngc-icon ngc-icon-bold",
							action:function($event){
								cmd($event,"bold");
							}
						},{
							icon:"ngc-icon ngc-icon-italic",
							action:function($event){
								cmd($event,"italic");
							}
						},{
							icon:"ngc-icon ngc-icon-underline",
							action:function($event){
								cmd($event,"underline");
							}
						}
					]
				},{
					buttons:[
						{
							icon:"ngc-icon ngc-icon-indent",
							action:function($event){
								cmd($event,"indent");
							}
						},{
							icon:"ngc-icon ngc-icon-outdent",
							action:function($event){
								cmd($event,"outdent");
							}
						}
					]
				},{
					buttons:[
						{
							icon:"ngc-icon ngc-icon-align-left",
							action:function($event){
								cmd($event,"justifyLeft");
							}
						},{
							icon:"ngc-icon ngc-icon-align-center",
							action:function($event){
								cmd($event,"justifyCenter");
							}
						},{
							icon:"ngc-icon ngc-icon-align-right",
							action:function($event){
								cmd($event,"justifyRight");
							}
						},{
							icon:"ngc-icon ngc-icon-align-justify",
							action:function($event){
								cmd($event,"justifyFull");
							}
						}
					]
				},{
					buttons:[
						{
							icon:"ngc-icon ngc-icon-link",
							action:function($event){
								var dir = prompt("Itroduzca la dirección:");
								if(dir){
									s.doc.execCommand("createLink", false, dir);
								}
							}
						},{
							icon:"ngc-icon ngc-icon-unlink",
							action:function($event){
								cmd($event,"unlink");
							}
						},{
							icon:"ngc-icon ngc-icon-image",
							action:function($event){
								var dir = prompt("Itroduzca la dirección:");
								if(dir){
									s.doc.execCommand("insertImage", false, dir);
								}
							}
						},{
							icon:"ngc-icon ngc-icon-list-ul",
							action:function($event){
								cmd($event,"insertUnorderedList");
							}
						},{
							icon:"ngc-icon ngc-icon-list-ol",
							action:function($event){
								cmd($event,"insertOrderedList");
							}
						}
					]
				}
			];
			s.load = function(){
				$http({
					url:apiurl("MailTemplate")
				}).then(function(response){
					s.doc.body.innerHTML = typeof response.data.template !== "undefined" ?response.data.template:"";
					s.subject = response.data.subject;
				},function(error){})
			};
			s.save = function(){
				$http({
					url:apiurl("MailTemplateUpdate"),
					method:"POST",
					data:{template:s.doc.body.innerHTML,subject:s.subject}
				}).then(function(response){
					s.doc.body.innerHTML = typeof response.data.template !== "undefined" ?response.data.template:"";
					s.subject = response.data.subject;
				},function(error){
					
				})
			};
			s.load();
		}
	}
}])
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
			s.spinnerText = "Cargando...";
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
			};
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
}])
.directive("ngcExplorer",["$http",function($http){
	return {
		restrict:"A",
		require:"ngModel",
		scope:{path:"=ngModel"},
		templateUrl:turl("ngc-explorer.template.html"),
		link:function(s,e,a,c){
			s.fullPath = ".";
			s.read = function(path){
				$http({
					url:apiurl("ListPath"),
					params:{path:path}
				}).then(function(response){
					s.files = response.data.files;
					s.fullPath = response.data.path;
				})
			};
			s.open = function(file){
				if(file.type === "dir"){
					s.read(file.fullpath);
				}else{
					s.selected = file;
				}
			};
			s.read(s.fullPath)
		}
	};
}])
.directive("ngcLoading",["$http",function($http){
	return{
		restrict:"A",
		require:"ngModel",
		scope:{
			ngcLoading:"=ngcName",
			loading:"=ngModel"
		},
		templateUrl:turl("ngc-loading.template.html"),
		link:function(s,e,a){
			s.word = (a.ngcLoadingWord || "Cargando...");
			s.ngcLoading = {
				show:function(){
					s.loading = true;
				},
				hide:function(){
					s.loading = false;
				}
			};
		}
	};
}]);