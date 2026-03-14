<?php
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
$errors = $data['errors'] ?? [];
$old = $data['old'] ?? [];
$specialties = $data['specialties'] ?? [];
$is_edit = !empty($data['is_edit']);
$doctor_id = (int) ($data['doctor_id'] ?? 0)
    ?>
<section class="wall"
    style="min-height:100vh;display:grid;place-items:center;background:linear-gradient(100deg,#0b1e3b,#2f6fb0 70%,#68afd8)">
    <div class="card"
        style="width:min(640px,100%);padding:24px;border-radius:16px;background:#fff;box-shadow:0 10px 30px rgba(2,19,53,.12)">
        <h1 style="margin:0 0 4px;font-size:22px">
            <?= $is_edit ? 'Edit Doctor' : 'Add a Doctor' ?>
        </h1>
        <p style="margin:0 0 16px;color:#6b7380">Create a new doctor profile for your clinic.</p>

        <?php if ($errors): ?>
            <div
                style="background:#fde8e8;border:1px solid #f5c2c7;color:#7a1c1c;border-radius:10px;padding:10px 12px;margin-bottom:12px">
                <?php foreach ($errors as $e): ?>
                    <div><?= e($e) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post"
            action="<?= BASE_URL ?>index.php?page=office_doctor_new<?= $is_edit ? '&doctor_id=' . $doctor_id : '' ?>"
            style="display:grid;gap:12px">
            <!-- doctor_name (required, <=50) -->
            <label class="form__label">Full name
                <input name="doctor_name" required maxlength="50" value="<?= e($old['doctor_name'] ?? '') ?>"
                    placeholder="e.g., Dr. Nguyen Van A"
                    style="width:100%;height:44px;margin-top:6px;padding:10px 12px;border:1px solid #dbe2ea;border-radius:10px">
            </label>

            <!-- specialty_id (required) -->
            <label class="form__label">Specialty
                <select name="specialty_id" required
                    style="width:100%;height:44px;margin-top:6px;padding:10px 12px;border:1px solid #dbe2ea;border-radius:10px">
                    <option value="">-- Choose specialty --</option>
                    <?php foreach ($specialties as $s): ?>
                        <option value="<?= (int) $s['specialty_id'] ?>" <?= (string) ($old['specialty_id'] ?? '') === (string) $s['specialty_id'] ? 'selected' : '' ?>>
                            <?= e($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <!-- email (required) -->
            <label class="form__label">Email
                <input type="email" name="email" required maxlength="255" value="<?= e($old['email'] ?? '') ?>"
                    placeholder="name@example.com"
                    style="width:100%;height:44px;margin-top:6px;padding:10px 12px;border:1px solid #dbe2ea;border-radius:10px">
            </label>

            <!-- Optional fields that exist in DB -->
            <div style="display:grid;gap:12px;grid-template-columns:1fr 1fr">
                <label class="form__label">Degree (optional)
                    <input name="degree" maxlength="30" value="<?= e($old['degree'] ?? '') ?>"
                        placeholder="e.g., MD, DDS"
                        style="width:100%;height:44px;margin-top:6px;padding:10px 12px;border:1px solid #dbe2ea;border-radius:10px">
                </label>

                <label class="form__label">Graduate (optional)
                    <input name="graduate" maxlength="100" value="<?= e($old['graduate'] ?? '') ?>"
                        placeholder="e.g., University of Medicine HCMC"
                        style="width:100%;height:44px;margin-top:6px;padding:10px 12px;border:1px solid #dbe2ea;border-radius:10px">
                </label>
            </div>

            <label class="form__label">Photo URL (optional)
                <input name="photo" maxlength="255" value="<?= e($old['photo'] ?? '') ?>"
                    placeholder="https://…/avatar.jpg"
                    style="width:100%;height:44px;margin-top:6px;padding:10px 12px;border:1px solid #dbe2ea;border-radius:10px">
            </label>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
                <a href="<?= BASE_URL ?>index.php?page=office_dashboard" class="btn btn--ghost"
                    style="padding:10px 14px;border-radius:10px;border:1px solid #dbe2ea;text-decoration:none">Cancel</a>
                <button type="submit" class="btn btn--primary"
                    style="padding:10px 16px;border-radius:10px;background:#1663f9;color:#fff;border:0">
                    <?= $is_edit ? 'Save Changes' : 'Save Doctor' ?>
                </button>
            </div>
        </form>
    </div>
</section>
