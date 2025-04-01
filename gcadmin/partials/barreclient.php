<aside class="sidebar">
        <div class="sidebar-header">
            <img src="../admin/logo/Logo-ldap.png" alt="Logo" class="logo-sidebar">
            <?php if (isset($idclient) && isset($ClientsForm)): ?>
            <form method='POST' action="clientdetail.php" style='display:inline;'>
                <table>
                    <tbody>
                        <?php
                            $client = $ClientsForm->ClientsRecoveryById($idclient)[0];
                            $plateformes = $ClientsForm->PlateformeRecovery();
                            
                            echo "<tr>";
                            echo "<td>Nom du client<BR><INPUT type=text name='EditNom' value=\"".$client['Nom']."\"/>";
                            echo "<INPUT type='hidden'name='idclient' value='$idclient'>";
                            echo "</td>";
                            echo "</tr>";
                            
                            echo "<tr>";
                            echo "<td>Adresse e-mail<BR><INPUT type=text name='EditEmail' value=\"".$client['Email']."\"/></td>";
                            echo "</tr>";
                            
                            echo "<tr>";
                            echo "<td>Téléphone<BR><INPUT type=text name='EditTelephone' value=\"".$client['Telephone']."\"/></td>";
                            echo "</tr>";
                            
                            echo "<tr>";
                            echo "<td>Adresse<BR><INPUT type=text name='EditAdresse' value=\"".$client['Adresse']."\"/></td>";
                            echo "</tr>";
                            
                            echo "<tr>";
                            echo "<td>Plateforme<BR><SELECT name='EditPlateforme'>";
                            
                            foreach ($plateformes as $plateforme)
                            {
                                if ($plateforme['PlateformeNom'] == $client['Plateforme']) echo "<OPTION value=\"$plateforme[PlateformeNom]\" selected>$plateforme[PlateformeNom]</OPTION>";
                                else echo "<OPTION value=\"$plateforme[PlateformeNom]\">$plateforme[PlateformeNom]</OPTION>";
                            }
                            echo "</SELECT>";
                            echo "</td>";
                            echo "</tr>";
                            
                            echo "<tr>";
                            echo "<td>URL Plateforme<BR><INPUT type=text name='EditPlateformeURL' value=\"".$client['PlateformeURL']."\"/></td>";
                            echo "</tr>";
                            
                            echo "<tr>";
                            echo "<td><button name='EditClient' class='action-button'>Enregistrer</button></td>";
                            echo "</tr>";
                        ?>
                    </tbody>
                </table>
            </form>
            <?php endif; ?>
        </div>
    </aside>