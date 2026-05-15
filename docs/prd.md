# Dance Studio Booking & Check-in System（Myanmar）PRD（MVP）

## 1. 项目目标

### 1.1 背景问题
- 私教课排课依赖人工沟通（老师询问前台是否有空教室），效率低且容易产生冲突。
- 学生纸本签到导致出勤与课包扣减无法实时闭环，统计与对账成本高。

### 1.2 MVP 目标
- Teacher 能查看可用教室与可用时间段，并提交私教预约申请。
- Front Desk 能审核/确认/调整/取消私教预约，统一管理教室资源占用。
- Teacher/Front Desk 能为私教课签到（check-in），系统自动扣减学生课包次数并保留扣减流水。

### 1.3 多舞蹈室（多租户）原则
- 系统面向多个舞蹈室（Studio）长期演进：核心数据按 `studio_id` 隔离；业务规则与资源数量（如教室数、课型、课包类型）均可配置，不写死。

## 2. 用户角色

### 2.1 Admin
- 配置 Studio 基础信息、营业时间、Room、课型（Class Type）、课包类型（Package Type）。
- 管理用户与角色（Admin / Front Desk / Teacher）。
- 查看运营数据（预约、签到、课包消耗与流水）（MVP 先提供基础列表与筛选）。

### 2.2 Front Desk
- 查看全店排程（日/周）与各 Room 占用情况（Regular + Private）。
- 审核私教预约：确认、调整时间/教室、拒绝、取消。
- 执行签到与异常处理：学生无可用课包时记录“待处理”，后续补录购买或人工处理。

### 2.3 Teacher
- 查看可用时间/教室（只读）。
- 为学生发起私教预约申请（选择学生、时间段、时长、备注；教室可由前台最终确认）。
- 为自己授课的私教课签到，查看自己相关的预约与出勤记录。

## 3. MVP 功能范围

### 3.1 Studio 设置
- Studio 信息：名称、时区、币种、启用状态。
- Studio 业务配置：营业时间（后续可扩展为可预约窗口、缓冲时间等）。

### 3.2 Room 管理
- Room CRUD：名称、容量（可选）、启用状态。
- Room 数量可变，完全由 Admin 配置。

### 3.3 Teacher 管理
- Teacher 账号与资料维护（关联到系统登录用户）。
- Teacher 可见范围：仅自己的预约与相关数据（由权限控制）。

### 3.4 Student 管理
- 学生档案：姓名、电话（可选）、备注、启用状态。

### 3.5 Regular weekly schedule（固定周课表）
- Regular 班按周固定：星期 + 开始/结束时间 + Room + 课型 + Teacher（可选）。
- Regular 课表作为“资源占用事件”，用于展示与冲突计算。

### 3.6 Availability 查询
- 按日期/时间范围查询可用 Room：排除 Regular 班与已确认的私教预约占用。
- Teacher 视图以可用时间/可用教室为主；Front Desk 视图以全量排程为主。

### 3.7 Private lesson booking（私教预约）
- Teacher 提交预约申请（Pending）。
- Front Desk 审核：Confirmed / Rejected / Cancelled；允许调整时间/Room 后确认。
- 预约状态机（MVP）：Pending → Confirmed / Rejected / Cancelled；签到后为 Completed。

### 3.8 Student lesson package（学生课包）
- 课包类型可配置：堂数、有效期、售价、币种。
- 学生购买课包后生成可扣减余额与到期日。

### 3.9 Attendance check-in（签到）
- 对 Confirmed 的私教预约执行签到（Teacher/Front Desk）。
- 支持撤销签到（权限/时间窗口可配置，MVP 先给基础能力）。

### 3.10 Package deduction log（扣课流水）
- 签到为 Present 时自动扣课，并生成扣课流水。
- 支持撤销签到时回滚扣课流水（以流水记录实现可追溯）。

## 4. 暂时不做的功能
- 学生端自助预约/自助签到（App/小程序/网页）。
- 在线支付与自动续费（MVP 仅后台记录付款与课包购买）。
- 自动通知（短信/WhatsApp/Email）、日历订阅（iCal）。
- 老师提成/薪资、财务报表与发票体系。
- 等候名单、自动排队、自动分配教室与复杂优化算法。
- 多人私教、按分钟扣课、复杂优惠（折扣码、赠课规则等）。

## 5. 主要业务流程

### 5.1 初始化配置（Admin）
1) 创建 Studio、设置时区与币种。  
2) 配置 Room（数量可变）。  
3) 配置课型与课包类型。  
4) 创建 Teacher/Front Desk 账号并分配角色。  
5) 录入 Regular weekly schedule。  

### 5.2 私教预约（Teacher → Front Desk）
1) Teacher 查看某日可用时间/Room。  
2) Teacher 选择学生与时间段提交预约申请（Pending）。  
3) Front Desk 审核并确认（Confirmed）或拒绝/取消；如有需要可调整时间/Room 后确认。  

### 5.3 到店签到与扣课（Teacher/Front Desk）
1) 仅对 Confirmed 私教预约可签到。  
2) 签到为 Present 后，系统自动扣减学生课包次数并生成扣课流水。  
3) 若无可用课包：记录签到但标记扣课待处理，前台后续补录课包或处理异常。  

## 6. 预约冲突规则

### 6.1 冲突对象
- Regular 课表事件（占用 Room 时间段）
- 已确认的私教预约（Confirmed）

### 6.2 冲突判定（时间区间重叠）
当确认私教预约时，满足任一则冲突：
- Room 冲突：同一 Room 的占用时间与预约时间重叠。
- Teacher 冲突：同一 Teacher 在同一时间段存在已确认预约。

时间区间重叠定义：`[startA, endA)` 与 `[startB, endB)` 有交集即冲突；允许无缝衔接（`endA == startB` 不冲突）。

## 7. 签到与扣课规则
- 只有签到状态为 Present 才扣课包。
- Cancelled 的预约不扣课。
- 默认扣课单位：每次私教签到扣 1 次（后续可按课型配置扣减单位）。
- 扣课优先级：优先扣减“最早到期”的有效课包；无有效课包则进入待处理。
- 撤销签到：回滚对应扣课流水并恢复预约到可签到状态（具体权限与时间窗口后续细化）。

## 8. 技术与实现原则（MVP）
- 技术栈：Laravel（项目当前版本）、MySQL、Blade + Bootstrap。
- 业务逻辑放在 Service 层，Model 保持轻量（关系、fillable、casts）。
- 数据隔离：所有查询默认按 `studio_id` 过滤（后续可通过全局作用域/中间件实现）。

