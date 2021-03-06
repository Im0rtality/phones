<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CameraSpeedRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CameraSpeedRepository extends EntityRepository
{
    /**
     * @param CameraSpeed $stat
     */
    public function save(CameraSpeed $stat)
    {
        $em = $this->getEntityManager();

        $phone = $em->getRepository('PhonesPhoneBundle:Phone')->find($stat->getPhoneId());
        if ($phone) {
            $stat->setPhone($phone);

            $criteria = [
                'phoneId'     => $stat->getPhoneId(),
                'provider_id' => $stat->getProviderId(),
            ];

            $entityRez = $this->findBy($criteria);

            if (empty($entityRez)) {
                $em->persist($stat);
                $em->flush($stat);
            }
        }
    }
}
