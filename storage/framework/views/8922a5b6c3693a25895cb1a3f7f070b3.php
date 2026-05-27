<?php $__env->startSection('title', 'Create Regular Class'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .field { margin-bottom: 12px; }
        input, select { min-width: 240px; }
        .error { color: #991b1b; font-size: 12px; margin-top: 6px; }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Create Regular Class</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="<?php echo e(url('/regular-classes')); ?>">Back</a>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 18px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(url('/regular-classes')); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-row">
            <div class="field">
                <label>Weekdays</label>
                <div style="font-size: 12px; color: #6b7280; margin: -4px 0 8px 0;">
                    One Regular Class record represents one weekday schedule. Select multiple weekdays to create multiple weekly rules with the same teacher, room, time, and date range.
                    <br>
                    一条 Regular Class 代表一周中的某一天固定课。如果一个课程一周有多天，可以一次选择多个星期，系统会自动创建多条规则。
                </div>
                <?php
                    $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
                    $selectedWeekdays = old('weekdays', [1]);
                    $selectedWeekdays = is_array($selectedWeekdays) ? array_values(array_map('intval', $selectedWeekdays)) : [(int) $selectedWeekdays];
                ?>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 520px;">
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label style="display: flex; align-items: center; gap: 6px; margin: 0; font-size: 14px; color: #111827;">
                            <input type="checkbox" name="weekdays[]" value="<?php echo e($k); ?>" <?php if(in_array($k, $selectedWeekdays, true)): echo 'checked'; endif; ?>>
                            <?php echo e($label); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="field">
                <label for="start_time">Start Time</label>
                <input id="start_time" name="start_time" type="time" value="<?php echo e(old('start_time', '09:00')); ?>" required>
            </div>

            <div class="field">
                <label for="end_time">End Time</label>
                <input id="end_time" name="end_time" type="time" value="<?php echo e(old('end_time', '10:00')); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id" required>
                    <option value="">Select a room</option>
                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($room->id); ?>" <?php if((int) old('room_id') === $room->id): echo 'selected'; endif; ?>><?php echo e($room->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="field">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id">
                    <option value="">(None)</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>" <?php if((int) old('teacher_id') === $teacher->id): echo 'selected'; endif; ?>>
                            <?php echo e($teacher->display_name ?? ('#'.$teacher->id)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="field">
                <label for="class_type_id">Class Type</label>
                <select id="class_type_id" name="class_type_id" required>
                    <option value="">Select a class type</option>
                    <?php $__currentLoopData = $classTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ct->id); ?>" <?php if((int) old('class_type_id') === $ct->id): echo 'selected'; endif; ?>><?php echo e($ct->name); ?> (<?php echo e($ct->kind); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="field">
            <label for="title">Title *</label>
            <input id="title" name="title" type="text" maxlength="120" value="<?php echo e(old('title')); ?>" required>
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div style="font-size: 12px; color: #6b7280; margin: -6px 0 12px 0;">
            Regular Class repeats weekly on the selected day/time. starts_on / ends_on only limit the effective date range.
            <br>
            Regular Class 是每周重复的固定课。starts_on / ends_on 只是用来限制生效日期范围。
        </div>

        <div class="form-row">
            <div class="field">
                <label for="starts_on">Starts On</label>
                <input id="starts_on" name="starts_on" type="date" value="<?php echo e(old('starts_on')); ?>">
            </div>
            <div class="field">
                <label for="ends_on">Ends On</label>
                <input id="ends_on" name="ends_on" type="date" value="<?php echo e(old('ends_on')); ?>">
            </div>
            <div class="field">
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="1" <?php if(old('is_active', '1') === '1'): echo 'selected'; endif; ?>>Yes</option>
                    <option value="0" <?php if(old('is_active') === '0'): echo 'selected'; endif; ?>>No</option>
                </select>
            </div>
        </div>

        <div style="font-size: 12px; color: #6b7280; margin: -6px 0 12px 0;">
            If this is a long-term weekly class, leave starts_on and ends_on empty.
            <br>
            如果这是长期固定课，开始日期和结束日期可以留空。
        </div>

        <button class="btn" type="submit">Submit</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/regular-classes/create.blade.php ENDPATH**/ ?>