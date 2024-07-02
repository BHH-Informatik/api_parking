package dev.enten.models

import java.util.UUID

// enum class PERMISSIONS {
// 	ALL: 0,
// 	USER: 1,
// 	ADMIN: 2
// }



data class Role(
  val id: UUID,
  val name: String,
  val permission_level: Int
) {
	fun canAccess(permission: Int): Boolean {
		if(permission <= 0) return true
		return permission_level >= permission
	}
}
