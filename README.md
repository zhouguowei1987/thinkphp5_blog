**thinkphp5.0-个人博客系统**

部署步骤：

1、域名指向本项目文件夹；

2、创建数据库myblog，执行myblog.sql文件；

3、在application文件夹下创建database.php文件；

4、database.php文件内容是（根据实际情况自行修改）：

return [

    // 数据库类型
    'type'            => 'mysql',
    
    // 服务器地址
    'hostname'        => '127.0.0.1',
    
    // 数据库名
    'database'        => 'myblog',
    
    // 用户名
    'username'        => 'root',
    
    // 密码
    'password'        => 'root',
    
    // 端口
    'hostport'        => '3306',
    
    // 连接dsn
    'dsn'             => '',
    
    // 数据库连接参数
    'params'          => [],
    
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    
    // 数据库表前缀
    'prefix'          => 'zgw_',
    
    // 数据库调试模式
    'debug'           => true,
    
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    
    // 指定从服务器序号
    'slave_no'        => '',
    
    // 自动读取主库数据
    'read_master'     => false,
    
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    
    // 数据集返回类型
    'resultset_type'  => 'array',
    
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
    
];