package dev.enten.utils

import com.zaxxer.hikari.HikariConfig;
import com.zaxxer.hikari.HikariDataSource;
import javax.sql.DataSource;

class Database(

    private val driver: String = "mysql",
    private val host: String = "localhost",
    private val port: Int = 3306,
    private val database: String = "parking",
    private val username: String = "root",
    private val password: String = "password"

) {
    fun dataSource(): DataSource {
        val config = HikariConfig()
        config.jdbcUrl = "jdbc:$driver://$host:$port/$database"
        config.username = username
        config.password = password
        config.addDataSourceProperty("cachePrepStmts", "true")
        config.addDataSourceProperty("prepStmtCacheSize", "250")
        config.addDataSourceProperty("prepStmtCacheSqlLimit", "2048")

        return HikariDataSource(config)
    }
}