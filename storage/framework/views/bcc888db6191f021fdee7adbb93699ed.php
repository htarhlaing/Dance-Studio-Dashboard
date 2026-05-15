<?php $__env->startSection('title', 'Create Private Booking'); ?>

<?php $__env->startPush('styles'); ?>
        <style>
            .row { display: flex; gap: 12px; align-items: end; margin-bottom: 16px; flex-wrap: wrap; }
            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input, select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; min-width: 220px; }
            button { padding: 7px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 6px; cursor: pointer; }
            .muted { color: #6b7280; font-size: 12px; margin-top: 6px; }
            .alert { padding: 10px 12px; border-radius: 8px; margin: 12px 0; }
            .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
            .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
            .field { margin-bottom: 12px; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <h1>Create Private Booking</h1>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo e(url('/private-bookings')); ?>">
            <?php echo csrf_field(); ?>

            <input type="hidden" name="date" value="<?php echo e(old('date', $date)); ?>">
            <input type="hidden" name="room_id" value="<?php echo e(old('room_id', $roomId)); ?>">
            <input type="hidden" name="start_time" value="<?php echo e(old('start_time', $startTime)); ?>">
            <input type="hidden" name="end_time" value="<?php echo e(old('end_time', $endTime)); ?>">
            <input type="hidden" name="class_type_id" value="<?php echo e(old('class_type_id', $classTypeId)); ?>">
            <input type="hidden" name="duration" value="<?php echo e(old('duration', $duration)); ?>">

            <div class="field">
                <label>Date</label>
                <div><?php echo e(old('date', $date)); ?></div>
            </div>
            <div class="field">
                <label>Room</label>
                <div><?php echo e($room?->name ?? ('#'.old('room_id', $roomId))); ?></div>
            </div>
            <div class="field">
                <label>Start Time</label>
                <div><?php echo e(old('start_time', $startTime)); ?></div>
            </div>
            <div class="field">
                <label>End Time</label>
                <div><?php echo e(old('end_time', $endTime)); ?></div>
                <?php if(old('duration', $duration)): ?>
                    <div class="muted">Duration: <?php echo e(old('duration', $duration)); ?> minutes</div>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="student_id">Student</label>
                <select id="student_id" name="student_id" required>
                    <option value="">Select a student</option>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>" <?php if((int) old('student_id') === $student->id): echo 'selected'; endif; ?>><?php echo e($student->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="field">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id" required>
                    <option value="">Select a teacher</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>" <?php if((int) old('teacher_id') === $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->display_name ?? ('#'.$teacher->id)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <button type="submit">Submit</button>
        </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/private-bookings/create.blade.php ENDPATH**/ ?>