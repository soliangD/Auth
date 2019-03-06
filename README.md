## Auth 配置

**1、配置文件**    
复制      
JAuth/config/JAuth.php 到 /config       
JAuth/config/auth.php 到 /config
修改配置为所需  

**2、修改 /bootstrap/app.php**

    //取消注释      
    $app->withFacades();
    
    //取消注释      
    $app->withEloquent();
    
    //添加配置    
    $app->configure('JAuth');   
    $app->configure('auth');
    
    //注册服务
    $app->register(Yunhan\JAuth\AuthServiceProvider::class);

**3、表迁移**             
复制      
JAuth/database/mgrations/create_ticket_table.php 到 database/migrations/2018_01_01_000000_create_ticket_table.php     
JAuth/database/mgrations/create_users_table.php 到 database/migrations/2018_01_01_000000_create_users_table.php

php artisan migrate

**5、配置用户model**          

user model 引入trait

    use Authenticatable, Authorizable, JAuthTrait, SsoTrait;

可重写JAuthTrait中方法进行相应配置

model例：

    <?php

    namespace App;
    
    use Illuminate\Auth\Authenticatable;
    use Yunhan\JAuth\Traits\JAuthTrait;
    use Yunhan\JAuth\Traits\SsoTrait;
    use Laravel\Lumen\Auth\Authorizable;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
    use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
    
    class User extends Model implements AuthenticatableContract, AuthorizableContract
    {
        use Authenticatable, Authorizable;
        use JAuthTrait, SsoTrait;
    
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name', 'email',
        ];
    
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        protected $hidden = [
            'password',
        ];
    }
    
**6、配置中间件**         
可将 src/Middleware/JAuth.php 复制出来自定义或直接使用

bootstrap/app.php 中添加路由中间件 

     $app->routeMiddleware([
         'JAuth' => Yunhan\JAuth\Middleware\JAuthMiddleware::class,
     ]);

使用：   
需传递两个参数，user表示当前接口使用的guard(在config/auth.php配置)，第二个参数表示当前接口需登录状态。        
第二个参数传任意值表示需登录


    $router->get('test', [
        'middleware' => 'JAuth:user,1',
        'uses' => 'Controller@test'
    ]);
    
    $router->group(['middleware' => 'JAuth:user,1'], function (\Laravel\Lumen\Routing\Router $api) {
        $api->get('test', 'Controller@test');
    });
    
> 建议为每个接口配置此中间件，无需登录认证的接口第二个参数不传即可

## 使用

### guard 配置

dirver支持：token、sso

token：

### token

填充user数据        
//php artisan db:seed


登录

    $router->get('login', 'Controller@login');

    use Yunhan\JAuth\Auth;

    public function login()
    {
        //执行相应用户名密码认证
        //...
        $uid = 1;
        //token操作
        $token = Auth::login($uid, $guard);
        return $token;
    }
    
注销登录

    $router->get('logout', [
        'middleware' => 'JAuth:user,1',
        'uses' => 'Controller@logout'
    ]);
    
    
    use Yunhan\JAuth\Auth;
    
    public function logout()
    {
        Auth::logout();
        //...
    }
    
获取当前登录用户信息

    $router->get('selfInfo', [
        'middleware' => 'JAuth:user,1',
        'uses' => 'Controller@selfInfo'
    ]);
    
    use Yunhan\JAuth\Auth;
    
    public function selfInfo()
    {
        // 获取用户信息，可在user model 内重写JAuthTrait的 getUserByIdToJAuth() 方法定义返回user实例
        $user = Auth::user();
        // 获取id
        $id = Auth::id();
        // 获取用户额外绑定身份信息，在user model内重写JAuthTrait的 accessIdentity() 方法进行身份信息的提供
        $access = Auth::identity();
        return $user->email;
    }
    
### sso 

sso暂不提供login和logout功能

    $router->get('selfInfo', [
        'middleware' => 'JAuth:user,1',
        'uses' => 'Controller@selfInfo'
    ]);
    
    use Yunhan\JAuth\Auth;
    
    public function selfInfo()
    {
        // 获取用户信息，可在user model 内重写SsoTrait的 getUserByTokenToSso() 方法定义返回user实例
        $user = Auth::user();
        // 获取id
        $id = Auth::id();
        // 获取用户额外绑定身份信息，在user model内重写JAuthTrait的 accessIdentity() 方法进行身份信息的提供
        $access = Auth::identity();
        return $user->email;
    }
    
### 自定义user与identity

JAuthTrait内定义有user返回方式与identity返回值得方法，可进行重写自定义返回。

SsoTrait内定义有 sso driver返回user方式，可进行重写自定义返回。