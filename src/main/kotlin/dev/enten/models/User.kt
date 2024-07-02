package dev.enten.models

import java.util.*

// User data class
data class User(
  val id: UUID,
  val username: String,
  val email: String,
  val password: String,
  val role: Role
) {
	fun canAcces(permission: Int): Boolean {
		if(permission <= 0) return true
		if(role == null) return false

		return role.canAccess(permission)
	}
}

// UserManager class
class UserManager {
    private val users = mutableListOf<User>()

    // Create a new user
    // fun createUser(name: String, email: String): User {
    //     val newUser = User(UUID.randomUUID(), name, email)
    //     users.add(newUser)
    //     return newUser
    // }

    // Edit an existing user
    // fun editUser(id: UUID, newName: String, newEmail: String): Boolean {
    //     val user = users.find { it.id == id }
    //     user?.let {
    //         it.name = newName
    //         it.email = newEmail
    //         return true
    //     }
    //     return false
    // }

    // Remove a user
    fun removeUser(id: UUID): Boolean {
        return users.removeIf { it.id == id }
    }

    // List all users
    fun listUsers(): List<User> = users
}