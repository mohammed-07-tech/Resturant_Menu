<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/db.php';

// handle create
if (isset($_POST['__action']) && $_POST['__action'] === 'create') {
    $q = db()->prepare("INSERT INTO dishes(nom,description,prix,categorie,image_url) VALUES (?,?,?,?,?)");
    $q->execute([
        trim($_POST['nom']),
        trim($_POST['description']),
        (float)$_POST['prix'],
        $_POST['categorie'],
        trim($_POST['image_url']) ?: null
    ]);
    header('Location: dashboard.php');
    exit;
}
// handle update
if (isset($_POST['__action']) && $_POST['__action'] === 'update' && !empty($_POST['id'])) {
    $q = db()->prepare("UPDATE dishes SET nom=?, description=?, prix=?, categorie=?, image_url=? WHERE id=?");
    $q->execute([
        trim($_POST['nom']),
        trim($_POST['description']),
        (float)$_POST['prix'],
        $_POST['categorie'],
        trim($_POST['image_url']) ?: null,
        (int)$_POST['id']
    ]);
    header('Location: dashboard.php');
    exit;
}
// handle delete
if (isset($_POST['__action']) && $_POST['__action'] === 'delete' && !empty($_POST['id'])) {
    $q = db()->prepare("DELETE FROM dishes WHERE id=?");
    $q->execute([(int)$_POST['id']]);
    header('Location: dashboard.php');
    exit;
}

// fetch data
$rows = db()->query("SELECT * FROM dishes ORDER BY FIELD(categorie,'Entrée','Plat','Dessert','Boisson'), nom")->fetchAll();
$counts = db()->query("SELECT categorie, COUNT(*) n FROM dishes GROUP BY categorie")->fetchAll();
$byCat = ['Entrée' => 0, 'Plat' => 0, 'Dessert' => 0, 'Boisson' => 0];
foreach ($counts as $c) {
    $byCat[$c['categorie']] = (int)$c['n'];
}
$total = array_sum($byCat);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — Tableau de bord</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>

<body class="admin-body">
    <header class="admin-header">
        <div class="header-content container">
            <div class="admin-title">
                <i class="fas fa-clipboard-list" style="color:var(--accent)"></i>
                <h1>Tableau de bord</h1>
            </div>
            <div class="admin-user">
                <span><?= htmlspecialchars($_SESSION['admin_email'] ?? 'admin') ?></span>
                <a class="logout-btn" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- STATS -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?= $byCat['Entrée'] ?></div>
                <div class="stat-label">Entrées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $byCat['Plat'] ?></div>
                <div class="stat-label">Plats</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $byCat['Dessert'] ?></div>
                <div class="stat-label">Desserts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $byCat['Boisson'] ?></div>
                <div class="stat-label">Boissons</div>
            </div>
        </section>

        <div class="actions" style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
            <!-- NOTE: data-open-add-modal is used by admin.js as a fallback if inline onclick isn't available -->
            <button type="button" class="btn btn-success" data-open-add-modal onclick="openModal('addModal')">
                <i class="fas fa-plus"></i> Ajouter un plat
            </button>
            <a class="btn btn-secondary" href="../menu.html" target="_blank"><i class="fas fa-eye"></i> Voir le menu</a>
            <span style="margin-left:auto;opacity:.75">Total: <strong><?= $total ?></strong></span>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Image</th>
                        <th style="width:200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td><?= htmlspecialchars($r['nom']) ?></td>
                            <td><span class="category-badge"><?= htmlspecialchars($r['categorie']) ?></span></td>
                            <td><?= number_format((float)$r['prix'], 2) ?> €</td>
                            <td>
                                <?php if (!empty($r['image_url'])): ?>
                                    <img class="dish-image" src="<?= htmlspecialchars($r['image_url']) ?>" alt="Aperçu plat"
                                        onerror="this.src='https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=400&q=80'">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick='fillEdit(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>
                                    <i class="fas fa-pen"></i> Éditer
                                </button>
                                <button class="btn btn-danger" onclick="confirmDelete('Supprimer « <?= htmlspecialchars($r['nom']) ?> » ?',()=>postDelete(<?= (int)$r['id'] ?>))">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- MODALS -->
    <div class="modal" id="addModal" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Ajouter un plat</h2>
                <button class="close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Good formulaire to add dish -->
                <form method="post" class="needs-validate" novalidate>
                    <input type="hidden" name="__action" value="create">
                    <div class="form-group">
                        <label>Nom</label>
                        <input required type="text" name="nom" placeholder="Ex: Bœuf de Charolais" minlength="2" maxlength="150">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Ex: Côte de bœuf grillée, légumes de saison (optionnel)"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Prix (€)</label>
                        <input required type="number" step="0.01" min="0" name="prix" value="0">
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select required name="categorie">
                            <option value="">Choisir…</option>
                            <option>Entrée</option>
                            <option>Plat</option>
                            <option>Dessert</option>
                            <option>Boisson</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image (URL)</label>
                        <input type="url" id="add_image_url" name="image_url" placeholder="https://…">
                        <!-- live preview appears here -->
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
                        <button class="btn btn-secondary" type="button" onclick="closeModal('addModal')">Annuler</button>
                        <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="editModal" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-pen"></i> Modifier un plat</h2>
                <button class="close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" class="needs-validate" novalidate>
                    <input type="hidden" name="__action" value="update">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group"><label>Nom</label><input required type="text" id="edit_nom" name="nom" minlength="2" maxlength="150"></div>
                    <div class="form-group"><label>Description</label><textarea id="edit_description" name="description"></textarea></div>
                    <div class="form-group"><label>Prix (€)</label><input required type="number" step="0.01" min="0" id="edit_prix" name="prix"></div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select required id="edit_categorie" name="categorie">
                            <option>Entrée</option>
                            <option>Plat</option>
                            <option>Dessert</option>
                            <option>Boisson</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image (URL)</label>
                        <input type="url" id="edit_image_url" name="image_url" placeholder="https://…">
                        <!-- live preview appears here -->
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
                        <button class="btn btn-secondary" type="button" onclick="closeModal('editModal')">Annuler</button>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- tiny helpers -->
    <form id="deleteForm" method="post" style="display:none">
        <input type="hidden" name="__action" value="delete">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <script src="../js/admin.js"></script>
    <script>
        initAdmin();

        function fillEdit(d) {
            document.getElementById('edit_id').value = d.id;
            document.getElementById('edit_nom').value = d.nom || '';
            document.getElementById('edit_description').value = d.description || '';
            document.getElementById('edit_prix').value = d.prix || 0;
            document.getElementById('edit_categorie').value = d.categorie || 'Plat';
            document.getElementById('edit_image_url').value = d.image_url || '';
            openModal('editModal');
        }

        function postDelete(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    </script>
</body>

</html>