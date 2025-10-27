<?php
/**
 * SQL Display Component
 * Displays SQL queries in a collapsible format
 */

function displaySQL($sql, $title = "SQL Query", $id = null)
{
    if ($id === null) {
        $id = 'sql_' . md5($sql . rand());
    }

    // Format SQL for better readability
    $formatted_sql = htmlspecialchars($sql);
    $formatted_sql = preg_replace('/\b(SELECT|FROM|WHERE|JOIN|LEFT JOIN|INNER JOIN|RIGHT JOIN|GROUP BY|ORDER BY|HAVING|LIMIT|INSERT INTO|UPDATE|DELETE|SET|VALUES|AS|ON|AND|OR|DISTINCT|COUNT|AVG|SUM|MAX|MIN|CASE|WHEN|THEN|ELSE|END)\b/i', '<span class="sql-keyword">$1</span>', $formatted_sql);

    ?>
    <div class="sql-display-container mb-3">
        <button class="btn btn-sm btn-outline-info sql-toggle-btn" type="button"
            onclick="toggleSQLDisplay('<?php echo $id; ?>', this)">
            <i class="fas fa-code"></i> <?php echo htmlspecialchars($title); ?> <i
                class="fas fa-chevron-down toggle-icon"></i>
        </button>
        <div class="sql-collapse-content" id="<?php echo $id; ?>" style="display: none;">
            <div class="sql-code-block">
                <div class="sql-header">
                    <span class="badge bg-info"><i class="fas fa-database"></i> SQL</span>
                    <button class="btn btn-sm btn-outline-light copy-sql-btn"
                        onclick="copySQLToClipboard('<?php echo $id; ?>_code')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <pre class="sql-code" id="<?php echo $id; ?>_code"><?php echo $formatted_sql; ?></pre>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Display SQL with result table
 */
function displaySQLWithResult($sql, $results, $title = "SQL Query", $id = null)
{
    displaySQL($sql, $title, $id);

    if (!empty($results)) {
        ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <?php foreach (array_keys($results[0]) as $column): ?>
                            <th><?php echo htmlspecialchars($column); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    } else {
        echo '<p class="text-muted"><em>No results found.</em></p>';
    }
}
?>

<style>
    .sql-display-container {
        margin: 1rem 0;
    }

    .sql-toggle-btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .sql-toggle-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .sql-toggle-btn.active {
        background-color: #0dcaf0;
        color: white;
        border-color: #0dcaf0;
    }

    .toggle-icon {
        margin-left: 0.5rem;
        transition: transform 0.3s ease;
    }

    .sql-collapse-content {
        margin-top: 0.75rem;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .sql-code-block {
        background: #1e293b;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .sql-header {
        background: #334155;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #475569;
    }

    .sql-code {
        margin: 0;
        padding: 1.25rem;
        background: #1e293b;
        color: #e2e8f0;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.9rem;
        line-height: 1.6;
        overflow-x: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .sql-keyword {
        color: #60a5fa;
        font-weight: bold;
    }

    .copy-sql-btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    .copy-sql-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }
</style>

<script>
    function toggleSQLDisplay(elementId, button) {
        const element = document.getElementById(elementId);
        const icon = button.querySelector('.toggle-icon');

        if (element.style.display === 'none') {
            element.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            button.classList.add('active');
        } else {
            element.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            button.classList.remove('active');
        }
    }

    function copySQLToClipboard(elementId) {
        const codeElement = document.getElementById(elementId);
        const text = codeElement.textContent;

        navigator.clipboard.writeText(text).then(() => {
            // Show success feedback
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-light');

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-light');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('Failed to copy SQL to clipboard');
        });
    }
</script>