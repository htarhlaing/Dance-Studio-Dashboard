<?php $__env->startSection('title', 'Reschedule Booking'); ?>

<?php $__env->startPush('styles'); ?>
        <style>
            input, select { min-width: 220px; }
            .muted { color: #6b7280; font-size: 12px; margin-top: 6px; }
            .field { margin-bottom: 12px; }
        </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Reschedule Booking</h1>
            </div>
            <div class="page-actions">
                <a class="btn btn-secondary" href="<?php echo e(url('/private-bookings')); ?>">Back</a>
            </div>
        </div>

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

        <form method="post" action="<?php echo e(url('/private-bookings/'.$booking->id.'/reschedule')); ?>">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label>Student</label>
                <div><?php echo e($booking->student?->name); ?></div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="<?php echo e(old('date', $booking->start_at?->format('Y-m-d'))); ?>" required>
                </div>
                <div class="field">
                    <label for="start_time">Start Time</label>
                    <input id="start_time" name="start_time" type="time" value="<?php echo e(old('start_time', $booking->start_at?->format('H:i'))); ?>" required>
                </div>
                <div class="field">
                    <label for="end_time">End Time</label>
                    <input id="end_time" name="end_time" type="time" value="<?php echo e(old('end_time', $booking->end_at?->format('H:i'))); ?>" required>
                </div>
            </div>

            <?php if($role === 'teacher'): ?>
                <input type="hidden" name="room_id" value="<?php echo e((int) $booking->room_id); ?>">
                <input type="hidden" name="teacher_id" value="<?php echo e((int) $booking->teacher_id); ?>">

                <div class="field">
                    <label>Room</label>
                    <div><?php echo e($booking->room?->name); ?></div>
                </div>
                <div class="field">
                    <label>Teacher</label>
                    <div><?php echo e($booking->teacher?->display_name ?? $booking->teacher_id); ?></div>
                </div>
            <?php else: ?>
                <div class="field">
                    <label for="room_id">Room</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">Select a room</option>
                        <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($room->id); ?>" <?php if((int) old('room_id', (int) $booking->room_id) === $room->id): echo 'selected'; endif; ?>><?php echo e($room->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="field">
                    <label for="teacher_id">Teacher</label>
                    <select id="teacher_id" name="teacher_id" required>
                        <option value="">Select a teacher</option>
                        <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($teacher->id); ?>" <?php if((int) old('teacher_id', (int) $booking->teacher_id) === $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->display_name ?? ('#'.$teacher->id)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endif; ?>

            <button class="btn" type="submit">Save Reschedule</button>
        </form>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/goldlife/Projects/DanceApp/resources/views/dance-studio/private-bookings/reschedule.blade.php ENDPATH**/ ?>