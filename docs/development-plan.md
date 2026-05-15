# Development Plan（MVP）

> 项目：Dance Studio Booking & Check-in System  
> 技术：Laravel（项目当前版本）+ MySQL + Blade + Bootstrap  
> 原则：第一版保持简单；多舞蹈室可扩展；业务逻辑放 Service；Model 只做关系与基础属性。

## 1. 范围与里程碑

### 1.1 MVP 范围（对齐 PRD）
1) Studio 设置  
2) Room 管理  
3) Teacher 管理  
4) Student 管理  
5) Regular weekly schedule  
6) Availability 查询  
7) Private lesson booking  
8) Student lesson package  
9) Attendance check-in  
10) Package deduction log  

### 1.2 交付里程碑（建议顺序）
- M1：数据模型与迁移（Studios/Rooms/Teachers/Students/ClassTypes/Packages/Bookings/Attendance/Ledger/Payments）
- M2：基础后台界面（Blade + Bootstrap）与 CRUD（Studio/Room/Teacher/Student/ClassType/PackageType）
- M3：Regular 周课表录入与展示（按 Room 日/周视图）
- M4：Availability 查询（按日期范围展示可用 Room 与空档）
- M5：Private Booking 申请/确认流程（含冲突校验与状态流转）
- M6：签到与自动扣课（Present 扣课、撤销签到回滚流水、Cancelled 不扣课）
- M7：列表筛选与对账视图（预约、签到、扣课流水）

## 2. 架构与目录约定

### 2.1 分层
- Controller：只处理请求/响应与权限校验的编排，不写复杂业务规则。
- Service：承载业务规则（可用性计算、冲突校验、预约确认、扣课与回滚）。
- Model：只保留 `fillable`、`casts`、`SoftDeletes`、关系定义。

### 2.2 推荐命名（不写死业务）
- Studio/Room/Teacher/Student/ClassType/PackageType/StudentPackage/PrivateBooking/AttendanceRecord/PackageTransaction/Payment
- 状态字段使用枚举值（字符串）存储，避免写死为数字魔法值；可在 Service 内集中维护映射。

## 3. 关键业务实现策略（MVP）

### 3.1 多舞蹈室（多租户）
- 所有读写操作默认按 `studio_id` 过滤。
- 角色权限的作用域默认限制在所属 `studio_id` 下。

### 3.2 冲突与可用性（重点）
- 冲突对象：Regular 课表占用 + 已确认的 Private Booking（Confirmed）。
- 重叠判定：`existing.start_at < new.end_at AND existing.end_at > new.start_at`。
- 并发安全：确认预约时使用 DB 事务 + 行级锁（`FOR UPDATE`）锁定可能冲突的记录范围，避免并发下重复确认。

### 3.3 签到与扣课
- 仅 `Present` 签到触发扣课。
- `Cancelled` 不扣课。
- 扣课以“流水账（ledger）”记录：每次扣课写入 `package_transactions`（负数），撤销写入回滚流水（正数）。
- 扣课优先策略：优先扣最早到期的有效课包（Service 计算并更新 `student_packages.remaining_units`）。

## 4. 权限与角色（MVP）

- Admin：全权限（同 studio）
- Front Desk：预约审核、改期/取消、签到与异常处理（同 studio）
- Teacher：创建预约申请、查看自己的预约、为自己预约签到（同 studio）

## 5. 数据质量与约束

### 5.1 DB 约束（辅助）
- 使用外键保持引用完整性（cascade/nullOnDelete 视业务表含义选择）。
- 对 `rooms`、`class_types`、`package_types` 在同一 studio 下做名称唯一。
- 对 `private_bookings` 提供 `room_id + start_at`、`teacher_id + start_at` 唯一约束作为兜底，但不替代“区间重叠”校验。

### 5.2 审计与可追溯（MVP）
- 预约的创建/确认/取消记录操作者（user_id）与时间戳字段。
- 扣课流水保留来源（attendance_record_id / payment_id / created_by_user_id）。

## 6. UI 计划（Blade + Bootstrap）

> 仅规划，不在本阶段实现。

- 后台菜单（建议）：Studio、Rooms、Teachers、Students、Class Types、Package Types、Regular Schedule、Private Bookings、Attendance、Package Ledger、Payments
- 关键页面：
  - 日/周排程：按 Room 展示 Regular + Private（Confirmed）
  - 可用性查询：输入日期/时间范围 → 返回可用 Room 与空档
  - 预约审核：待确认列表 + 详情页（调整时间/Room 后确认）

## 7. 测试与验收（MVP）

### 7.1 核心验收点
- Room/Teacher 冲突：同一时间段不能重复确认（并发下也不能）
- Availability：不会返回被 Regular 或 Confirmed 私教占用的 Room
- 签到扣课：Present 扣 1 次；Cancelled 不扣；撤销签到能回滚
- 课包扣减顺序：优先扣最早到期的有效课包

### 7.2 测试建议
- Feature tests 覆盖：
  - 预约确认冲突校验（room/teacher）
  - 签到触发扣课与回滚
  - 课包到期与余额不足处理

