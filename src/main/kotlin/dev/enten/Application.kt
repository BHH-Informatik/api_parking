package dev.enten

import dev.enten.plugins.*
import io.ktor.server.application.*
import dev.enten.utils.*
import java.sql.ResultSet

fun main(args: Array<String>) {
    io.ktor.server.netty.EngineMain.main(args)


	val db = Database()
	val connection = db.dataSource().connection
	try{
        val statement = connection.createStatement()
        val resultSet = statement.executeQuery("SELECT * FROM users")

        while (resultSet.next()) {
            // Assuming the users table has id, username, and email columns
            val id = resultSet.getInt("id")
            val username = resultSet.getString("username")
            val email = resultSet.getString("email")

            println("User: $id, $username, $email")
        }
	} catch (e: Exception) {
        e.printStackTrace()
    } finally {
        connection.close() // Ensure the connection is closed after use
    }
}

fun Application.module(testing: Boolean = false) {
    configureSerialization()
    configureMonitoring()
    configureHTTP()
    configureSecurity()
    configureRouting()
}
