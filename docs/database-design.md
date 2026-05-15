# 数据库设计（Laravel + MySQL，MVP）

> 目标：支持多舞蹈室（multi-studio）的 Regular 周课表、私教预约、签到与课包扣减流水。  
> 约定：主键统一 `BIGINT UNSIGNED` 自增；所有表包含 `created_at`、`updated_at`；需要软删除的表使用 `deleted_at`（SoftDeletes）。  
> 多租户：除 `studios` 外，业务表均包含 `studio_id` 并建立索引。

## 0. 重要约束说明（冲突防止）

MySQL 无法仅靠唯一索引完全阻止“时间区间重叠”的重复预约（例如 10:00-11:00 与 10:30-11:30）。因此需要：
- **应用层事务校验（必须）**：在确认（Confirmed）预约时，在事务内执行“重叠区间查询”并加锁（`SELECT ... FOR UPDATE`），避免并发写入造成重叠。
- **DB 索引兜底（辅助）**：提供 `room_id + start_at`、`teacher_id + start_at` 唯一约束，至少阻止“同一开始时间”的重复确认。

重叠判定：`existing.start_at < new.end_at AND existing.end_at > new.start_at`（半开区间，允许无缝衔接）。

---

## 1) studios

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| name | VARCHAR(120) | 舞蹈室名称 |
| timezone | VARCHAR(64) | 时区（例如 `Asia/Yangon`） |
| currency | CHAR(3) | 默认币种（例如 `MMK`） |
| phone | VARCHAR(40) NULL | 联系电话 |
| address | VARCHAR(255) NULL | 地址 |
| is_active | TINYINT(1) | 是否启用 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `PRIMARY KEY (id)`
- `INDEX studios_is_active (is_active)`

### 表之间关系
- `studios 1 - N rooms / users / teachers / students / class_types / package_types / regular_classes / private_bookings / attendance_records / student_packages / package_transactions / payments`

---

## 2) rooms（SoftDeletes）

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| name | VARCHAR(80) | 教室名称（教室数量不写死） |
| capacity | SMALLINT UNSIGNED NULL | 容量（可选） |
| is_active | TINYINT(1) | 是否可用 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |
| deleted_at | TIMESTAMP NULL | 软删除 |

### 主要索引
- `INDEX rooms_studio_id (studio_id)`
- `UNIQUE rooms_studio_name_unique (studio_id, name)`

### 表之间关系
- `rooms N - 1 studios`
- `rooms 1 - N regular_classes`
- `rooms 1 - N private_bookings`
- `rooms 1 - N attendance_records`

---

## 3) users

> 登录账号（Admin / Front Desk / Teacher）。Teacher 资料扩展在 `teachers.user_id`。学生不在 users 表。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| name | VARCHAR(120) | 姓名 |
| email | VARCHAR(191) NULL | 邮箱（可选，建议 studio 内唯一） |
| phone | VARCHAR(40) NULL | 手机（可选，建议 studio 内唯一） |
| password | VARCHAR(255) | 密码哈希 |
| role | ENUM('admin','front_desk','teacher') | 角色 |
| is_active | TINYINT(1) | 是否启用 |
| last_login_at | DATETIME NULL | 最近登录时间 |
| remember_token | VARCHAR(100) NULL | Laravel remember token |
| email_verified_at | TIMESTAMP NULL | 邮箱验证时间 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `INDEX users_studio_role (studio_id, role)`
- `INDEX users_studio_is_active (studio_id, is_active)`
- `UNIQUE users_studio_email_unique (studio_id, email)`（email 非 NULL 时）
- `UNIQUE users_studio_phone_unique (studio_id, phone)`（phone 非 NULL 时）

### 表之间关系
- `users N - 1 studios`
- `users 1 - 0..1 teachers`
- `users 1 - N private_bookings`（requested/confirmed/cancelled by）
- `users 1 - N attendance_records`（checked-in by / voided by）
- `users 1 - N package_transactions`（created by）

---

## 4) teachers（SoftDeletes）

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| user_id | BIGINT UNSIGNED FK | 对应登录用户 |
| display_name | VARCHAR(120) NULL | 显示名（可选） |
| bio | TEXT NULL | 简介（可选） |
| is_active | TINYINT(1) | 是否启用 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |
| deleted_at | TIMESTAMP NULL | 软删除 |

### 主要索引
- `UNIQUE teachers_user_unique (user_id)`
- `INDEX teachers_studio_is_active (studio_id, is_active)`

### 表之间关系
- `teachers N - 1 studios`
- `teachers 1 - 1 users`
- `teachers 1 - N regular_classes`
- `teachers 1 - N private_bookings`
- `teachers 1 - N attendance_records`

---

## 5) students（SoftDeletes）

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| name | VARCHAR(120) | 学生姓名 |
| phone | VARCHAR(40) NULL | 电话（可选） |
| notes | TEXT NULL | 备注（可选） |
| is_active | TINYINT(1) | 是否启用 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |
| deleted_at | TIMESTAMP NULL | 软删除 |

### 主要索引
- `INDEX students_studio_name (studio_id, name)`
- `UNIQUE students_studio_phone_unique (studio_id, phone)`（phone 非 NULL 时）

### 表之间关系
- `students N - 1 studios`
- `students 1 - N private_bookings`
- `students 1 - N attendance_records`
- `students 1 - N student_packages`
- `students 1 - N package_transactions`
- `students 1 - N payments`

---

## 6) class_types

> 课型可配置，不写死。既可用于 Regular，也可用于 Private。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| name | VARCHAR(120) | 课型名称 |
| kind | ENUM('regular','private','both') | 适用范围 |
| default_duration_minutes | SMALLINT UNSIGNED NULL | 默认时长（可选） |
| default_deduct_units | SMALLINT UNSIGNED | 默认扣课单位（默认 1） |
| is_active | TINYINT(1) | 是否启用 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `INDEX class_types_studio_kind (studio_id, kind)`
- `UNIQUE class_types_studio_name_unique (studio_id, name)`

### 表之间关系
- `class_types N - 1 studios`
- `class_types 1 - N regular_classes`
- `class_types 1 - N private_bookings`

---

## 7) package_types

> 课包类型可配置（堂数/有效期/价格），不写死。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| name | VARCHAR(120) | 课包名称 |
| lessons_count | SMALLINT UNSIGNED | 总堂数 |
| validity_days | SMALLINT UNSIGNED NULL | 有效期天数（NULL=不限） |
| price | DECIMAL(12,2) | 售价 |
| currency | CHAR(3) | 币种 |
| is_active | TINYINT(1) | 是否启用 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `INDEX package_types_studio_is_active (studio_id, is_active)`
- `UNIQUE package_types_studio_name_unique (studio_id, name)`

### 表之间关系
- `package_types N - 1 studios`
- `package_types 1 - N student_packages`

---

## 8) student_packages

> 学生购买后的课包实例（余额/到期日）。扣课流水记录在 `package_transactions`。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| student_id | BIGINT UNSIGNED FK | 学生 |
| package_type_id | BIGINT UNSIGNED FK | 课包类型 |
| payment_id | BIGINT UNSIGNED NULL FK | 对应付款（可为空，允许后补） |
| purchased_at | DATETIME | 购买时间 |
| expires_at | DATETIME NULL | 到期时间 |
| total_units | INT UNSIGNED | 总堂数（快照） |
| remaining_units | INT UNSIGNED | 剩余堂数 |
| status | ENUM('active','expired','void') | 状态 |
| notes | TEXT NULL | 备注 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `INDEX student_packages_student_active (studio_id, student_id, status, expires_at)`
- `INDEX student_packages_expires (studio_id, expires_at)`

### 表之间关系
- `student_packages N - 1 studios`
- `student_packages N - 1 students`
- `student_packages N - 1 package_types`
- `student_packages N - 0..1 payments`
- `student_packages 1 - N package_transactions`

---

## 9) regular_classes（SoftDeletes）

> Regular weekly schedule：周固定课表（占用教室资源）。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| room_id | BIGINT UNSIGNED FK | 教室 |
| teacher_id | BIGINT UNSIGNED NULL FK | 老师（可选） |
| class_type_id | BIGINT UNSIGNED FK | 课型 |
| day_of_week | TINYINT UNSIGNED | 1-7（建议 1=Mon…7=Sun） |
| start_time | TIME | 开始时间 |
| end_time | TIME | 结束时间 |
| starts_on | DATE NULL | 生效开始日期（可选） |
| ends_on | DATE NULL | 生效结束日期（可选） |
| is_active | TINYINT(1) | 是否启用 |
| notes | TEXT NULL | 备注 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |
| deleted_at | TIMESTAMP NULL | 软删除 |

### 主要索引
- `INDEX regular_classes_room_time (studio_id, room_id, day_of_week, start_time, end_time)`
- `INDEX regular_classes_teacher_time (studio_id, teacher_id, day_of_week, start_time, end_time)`
- `INDEX regular_classes_effective (studio_id, starts_on, ends_on, is_active)`

### 表之间关系
- `regular_classes N - 1 studios`
- `regular_classes N - 1 rooms`
- `regular_classes N - 0..1 teachers`
- `regular_classes N - 1 class_types`

---

## 10) private_bookings（SoftDeletes）

> 私教预约：指定日期时间、Room、Teacher、Student。  
> 只有 Confirmed 的预约占用资源并参与冲突；Cancelled 不扣课。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| student_id | BIGINT UNSIGNED FK | 学生 |
| teacher_id | BIGINT UNSIGNED FK | 老师 |
| room_id | BIGINT UNSIGNED FK | 教室 |
| class_type_id | BIGINT UNSIGNED NULL FK | 课型（可选） |
| start_at | DATETIME | 开始时间 |
| end_at | DATETIME | 结束时间 |
| status | ENUM('pending','confirmed','rejected','cancelled','completed') | 状态 |
| requested_by_user_id | BIGINT UNSIGNED FK | 发起人（登录用户） |
| confirmed_by_user_id | BIGINT UNSIGNED NULL FK | 确认人（登录用户） |
| cancelled_by_user_id | BIGINT UNSIGNED NULL FK | 取消人（登录用户） |
| cancelled_at | DATETIME NULL | 取消时间 |
| rejection_reason | VARCHAR(255) NULL | 拒绝原因 |
| notes | TEXT NULL | 备注 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |
| deleted_at | TIMESTAMP NULL | 软删除 |

### 主要索引
- `INDEX private_bookings_student_time (studio_id, student_id, start_at)`
- `INDEX private_bookings_teacher_time (studio_id, teacher_id, start_at, end_at)`
- `INDEX private_bookings_room_time (studio_id, room_id, start_at, end_at)`
- `INDEX private_bookings_status_time (studio_id, status, start_at)`
- `UNIQUE private_bookings_room_start_unique (studio_id, room_id, start_at)`（兜底：同开始时间不重复）
- `UNIQUE private_bookings_teacher_start_unique (studio_id, teacher_id, start_at)`（兜底：同开始时间不重复）

### 表之间关系
- `private_bookings N - 1 studios`
- `private_bookings N - 1 students`
- `private_bookings N - 1 teachers`
- `private_bookings N - 1 rooms`
- `private_bookings N - 0..1 class_types`
- `private_bookings N - 1 users`（requested/confirmed/cancelled）
- `private_bookings 1 - 0..1 attendance_records`

---

## 11) attendance_records

> 签到记录：只有 `status = present` 才触发扣课流水（`package_transactions`）。  
> 每个私教预约最多一条签到记录。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| private_booking_id | BIGINT UNSIGNED FK | 私教预约 |
| student_id | BIGINT UNSIGNED FK | 学生（冗余快照） |
| teacher_id | BIGINT UNSIGNED FK | 老师（冗余快照） |
| room_id | BIGINT UNSIGNED FK | 教室（冗余快照） |
| status | ENUM('present','absent','no_show') | 出勤状态 |
| checked_in_at | DATETIME | 签到时间 |
| checked_in_by_user_id | BIGINT UNSIGNED FK | 签到人（登录用户） |
| voided_at | DATETIME NULL | 撤销签到时间 |
| voided_by_user_id | BIGINT UNSIGNED NULL FK | 撤销人（登录用户） |
| notes | TEXT NULL | 备注 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `UNIQUE attendance_private_booking_unique (private_booking_id)`
- `INDEX attendance_studio_status_time (studio_id, status, checked_in_at)`
- `INDEX attendance_student_time (studio_id, student_id, checked_in_at)`

### 表之间关系
- `attendance_records N - 1 studios`
- `attendance_records N - 1 private_bookings`
- `attendance_records N - 1 students`
- `attendance_records N - 1 teachers`
- `attendance_records N - 1 rooms`
- `attendance_records N - 1 users`（checked-in/voided）
- `attendance_records 1 - N package_transactions`

---

## 12) package_transactions

> 课包扣减流水（ledger）：购买/扣课/撤销扣课/人工调整均记录在此，便于审计与对账。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| student_id | BIGINT UNSIGNED FK | 学生 |
| student_package_id | BIGINT UNSIGNED FK | 课包实例 |
| type | ENUM('purchase','deduction','void_deduction','adjustment') | 流水类型 |
| units_delta | INT | 堂数变动（扣课为负，回滚/购买为正） |
| balance_after | INT UNSIGNED | 变动后剩余堂数（快照） |
| occurred_at | DATETIME | 发生时间 |
| attendance_record_id | BIGINT UNSIGNED NULL FK | 来源：签到（扣课/撤销） |
| payment_id | BIGINT UNSIGNED NULL FK | 来源：付款（购买） |
| created_by_user_id | BIGINT UNSIGNED NULL FK | 操作人（登录用户） |
| notes | TEXT NULL | 备注 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `INDEX pkg_tx_student_time (studio_id, student_id, occurred_at)`
- `INDEX pkg_tx_package_time (studio_id, student_package_id, occurred_at)`
- `INDEX pkg_tx_type_time (studio_id, type, occurred_at)`

### 表之间关系
- `package_transactions N - 1 studios`
- `package_transactions N - 1 students`
- `package_transactions N - 1 student_packages`
- `package_transactions N - 0..1 attendance_records`
- `package_transactions N - 0..1 payments`
- `package_transactions N - 0..1 users`

---

## 13) payments

> 付款记录（MVP 可先后台录入）：用于对账并关联课包购买。

### 字段
| 字段 | 类型 | 说明 |
|---|---|---|
| id | BIGINT UNSIGNED PK AI | 主键 |
| studio_id | BIGINT UNSIGNED FK | 所属 studio |
| student_id | BIGINT UNSIGNED FK | 学生 |
| amount | DECIMAL(12,2) | 金额 |
| currency | CHAR(3) | 币种 |
| method | ENUM('cash','bank_transfer','card','other') | 支付方式 |
| status | ENUM('pending','paid','void','refunded') | 状态 |
| paid_at | DATETIME NULL | 支付时间 |
| reference | VARCHAR(120) NULL | 参考号/收据号（可选） |
| notes | TEXT NULL | 备注 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### 主要索引
- `INDEX payments_student_time (studio_id, student_id, paid_at)`
- `INDEX payments_status_time (studio_id, status, created_at)`
- `UNIQUE payments_studio_reference_unique (studio_id, reference)`（reference 非 NULL 时）

### 表之间关系
- `payments N - 1 studios`
- `payments N - 1 students`
- `payments 1 - N student_packages`
- `payments 1 - N package_transactions`

