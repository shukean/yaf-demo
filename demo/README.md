### 依赖的php扩展
#### php原生扩展
- mb_string  
- mysqli  

#### 第三方扩展
- yaf: https://github.com/laruence/yaf  
- toml: https://github.com/shukean/php-toml  
- ykloger: https://github.com/shukean/php-ykloger  

### php.ini 中增加yaf配置
```
[yaf]
yaf.environ = product | test
yaf.use_namespace=1
yaf.use_spl_autoload=1
```

### 目录结构
将demo重名为自己项目的名字, 存放代码目录  
将logs软链接到用于存放日志的目录, #`ln -s /path_to_log  logs`  

### 修改配置文件 
`<project>/conf/app.ini`  

#### 配置 mysql
```
mysql.1.*  为主库的配置
mysql.2.*  为从库1的配置  从库ID只能为数字, 字符串的ID可以用来当作额外的配置
mysql.3.*  为从库2的配置  从库ID只能为数字, 字符串的ID可以用来当作额外的配置
```

#### 配置好相应的环境
将<project>/public/index.\<env\>.php 文件重名为 index.php  

### HTTP服务器配置
Apache Nginx 的配置参考  https://github.com/laruence/yaf#rewrite-rules  
```
server {
  listen 80;
  server_name  127.0.0.1;
  root  path_to_site;
  index  index.php index.html index.htm;

  if (!-e $request_filename) {
    rewrite ^/(.*)  /index.php/$1 last;
  }

  location ~ \.php($|/) {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
  }
}
```

