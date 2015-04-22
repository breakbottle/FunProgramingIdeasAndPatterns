/**
 * Author: Clint Small Cain
 * Date: 4/21/2015
 * Time: 9:34 PM
 * Description:
 */
angular.module('app').factory('RequireJs',function($q){
    //requires services and factories files when needed.
    return function(requireList){
        require.config({
            baseUrl: '',
            paths: {//add your paths
                /*
                 'bilDebug':['app/common/debug'],
                 'test':['app/common/test']

                 Example:
                     Usage - call custom factory names, give list of variables names, then list of services and factories to inject
                     RequireJs(['bilDebug','test']).then(function(k){
                     console.log("what is k", )
                 })
                 */

            }
        });
        var promise = $q.defer();
        require(requireList,function(d){
            var inject =  angular.injector([application.name]);//application refers to angular.module('app')
            var resolved = [];
            if(angular.isArray(requireList)){
                angular.forEach(requireList,function(value,key){
                    var re = inject.get(value);
                    if(angular.isFunction(re)){
                        var objectIn = {};
                        objectIn[value] = re.call();
                        resolved.push(objectIn);
                    } else {
                        resolved.push(re);
                    }
                });
            }
            promise.resolve(resolved);
        });
        return promise.promise;

    };

});
